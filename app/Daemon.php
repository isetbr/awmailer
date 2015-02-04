<?php

# Getting autoload from Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/App.php';

# Loading resources
use Iset\Api\Callback;
use Iset\Api\Resource\Campaign;
use Iset\Model\ServiceTable;
use Iset\Model\CampaignTable;
use Iset\Model\QueueCollection;

# Initialzing Silex Application
$app = App::configure();
$app->boot();
$app['monolog.daemon']->addInfo('Initializing Daemon');
$app['monolog.daemon']->addInfo('Initializing application');

# Setting error handler
set_error_handler(function ($code, $message, $file, $line) use ($app) {
    $app['monolog.daemon']->addError('Internal error occurred', array('code'=>$code,'message'=>$message,'file'=>$file,'line'=>$line));
});

# Initializing gateway
$gateway = new CampaignTable($app);
$collection = new QueueCollection($app);
$serviceGateway = new ServiceTable($app);
$app['monolog.daemon']->addInfo('Initializing gateways');

# Forking process
$pid = pcntl_fork();
if ($pid) { exit(); }

# Setting session
$sess_id = posix_setsid();
$pid = posix_getpid();

# Configuring session
posix_seteuid($app['config']['service']['system']['uid']);
posix_setegid($app['config']['service']['system']['gid']);

$app['monolog.daemon']->addInfo('Daemon successfully started',array('PID'=>$pid,'SESS_ID'=>$sess_id));

# Initializing control vars
$loop_delay = (int) $app['config']['service']['daemon']['delay'];
$max_repeats = 10;
$repeated = array();

# Starting daemon
while (true) {
    # Getting active and paused campaigns
    $campaignsActive  = $gateway->getCampaignsByStatus(Campaign::STATUS_START);
    $campaignsPaused  = $gateway->getCampaignsByStatus(Campaign::STATUS_PAUSE);
    $campaignsStopped = $gateway->getCampaignsByStatus(Campaign::STATUS_STOP);
    $campaignsDone    = $gateway->getCampaignsByStatus(Campaign::STATUS_DONE);
    $campaigns = array_merge($campaignsActive,$campaignsPaused,$campaignsStopped);

    # Logging
    $app['monolog.daemon']->addNotice(
        'Found campaigns',
        array(
            'active'=>count($campaignsActive),
            'paused'=>count($campaignsPaused),
            'stopped'=>count($campaignsStopped),
            'done'=>count($campaignsDone),
        )
    );

    # Loop into results
    foreach ($campaigns as $campaign) {
        $campaignKey = $campaign->getCampaignKey();
        # Verifying if campaign is in cache
        $result = $app['cache']->hasItem($campaignKey);
        if ($result) {
            # Getting data from cache
            $data = json_decode($app['cache']->getItem($campaignKey),true);

            # Verifying if status has changed
            if ($campaign->status != Campaign::STATUS_START) {
                if (!is_null($campaign->pid) && posix_getpgid((int) $campaign->pid) != false) {
                    $command = "kill " . $campaign->pid;
                    exec($command);

                    # Logging
                    $app['monolog.daemon']->addNotice('Killing process',array('campaign'=>$campaignKey,'PID'=>$campaign->pid));

                    # Resolving context
                    switch ($campaign->status) {
                        case Campaign::STATUS_PAUSE :
                            $context = 'process_paused';
                            break;
                        case Campaign::STATUS_STOP :
                            $context = 'process_stopped';
                            break;
                    }

                    # Sending callback to the service
                    $callback = new Callback($app);
                    $callback->setService($serviceGateway->getServiceById($campaign->service));
                    $callback->setResource($campaign);
                    $callback->send(array('context'=>$context,'key'=>$campaignKey));
                    unset($callback);
                    continue;
                }
            }

            # Verifying if has previous
            if ($app['cache']->hasItem($campaignKey . '_previous')) {
                # Getting previous
                $previous = json_decode($app['cache']->getItem($campaignKey . '_previous'),true);

                # Verifying if is modified
                if ($data == $previous) {
                    # Verifying if repeated was set and increase counter
                    if (!isset($repeated[$campaignKey])) {
                        $repeated[$campaignKey] = 0;
                    }
                    $repeated[$campaignKey]++;

                    # Verifying if data was repeated at max loops
                    if ($repeated[$campaignKey] > $max_repeats || (isset($data['done']) && $data['done'] == 1)) {
                        # Updating campaign statuses
                        $campaign->sent = $data['sent'];
                        $campaign->fail = $data['fail'];
                        $campaign->progress = $data['progress'];
                        $campaign->pid = null;

                        # Verifying if process is done
                        if ($data['done'] == 1) {
                            $campaign->status = Campaign::STATUS_DONE;
                            $campaign->progress = 100;
                        }

                        # Saving campaign
                        $campaign->save();

                        # Logging
                        $app['monolog.daemon']->addNotice('Updating campaign',array('campaign'=>$campaignKey));

                        # Removing emails from queue
                        foreach ($data['success'] as $index => $email) {
                            if ($collection->remove($campaignKey,$email)) {
                                unset($data['success'][$index]);
                            }
                        }

                        # Cleaning cache
                        $app['cache']->removeItem($campaignKey);
                        $app['cache']->removeItem($campaignKey . "_previous");

                        # Resolving context
                        $context = ($data['done'] == 1) ? 'process_done' : 'process_error';

                        # Sending callback to the service
                        $callback = new Callback($app);
                        $callback->setService($serviceGateway->getServiceById($campaign->service));
                        $callback->setResource($campaign);
                        $callback->send(array('context'=>$context,'key'=>$campaignKey));
                        unset($callback);
                    }
                } else {
                    # Set previous cache
                    $app['cache']->setItem($campaignKey . "_previous", json_encode($data));

                    # Restarting counter of repeated cache
                    $repeated[$campaignKey] = 0;
                }
            } else {
                # Set previous cache
                $app['cache']->setItem($campaignKey . "_previous", json_encode($data));

                # Verifying if campaign just be started
                if ($campaign->status = Campaign::STATUS_START && !is_null($campaign->pid)) {
                    # Sending callback to the service
                    $callback = new Callback($app);
                    $callback->setService($serviceGateway->getServiceById($campaign->service));
                    $callback->setResource($campaign);
                    $callback->send(array('context'=>'process_started','key'=>$campaignKey));
                    unset($callback);
                }
            }
        } else {
            # Verifying if process is started and not running yet
            if ($campaign->status == Campaign::STATUS_START && is_null($campaign->pid)) {
                $app['monolog.daemon']->addNotice('Starting process',array('campaign'=>$campaignKey));
                $command = 'awmailer ' . $campaignKey . ' > /dev/null 2>&1';
                exec($command);
            }
        }
    }

    # Waiting...
    sleep($loop_delay);
}
