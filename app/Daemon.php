<?php

# Getting autoload from Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/App.php';

# Loading resources
use Iset\Model\Campaign;
use Iset\Model\CampaignTable;
use Iset\Model\QueueCollection;

# Initializing Service
define("PROCESS_TITLE",'m4a1d');
@cli_set_process_title(PROCESS_TITLE);

# Initialzing Silex Application
$app = App::configure();

# Initializing cache component
$cache = Zend\Cache\StorageFactory::factory($app['config']['cache']['zendcache']);
$cache->setOptions(array('cache_dir'=>$app['cache_path']));

# Initializing gateway
$gateway = new CampaignTable($app);
$collection = new QueueCollection($app);

# Forking process 
$pid = pcntl_fork();
if ($pid) { exit(); }

# Initializing control vars
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
    
    # Loop into results
    foreach ($campaigns as $campaign) {
        $campaignKey = $campaign->getCampaignKey();
        # Verifying if campaign is in cache
        $result = $cache->hasItem($campaignKey);
        if ($result) {            
            # Getting data from cache
            $data = json_decode($cache->getItem($campaignKey),true);
            
            # Verifying if status has changed
            if ($campaign->status != Campaign::STATUS_START) {
                if (!is_null($campaign->pid) && posix_getpgid((int)$campaign->pid) != false) {
                    $command = "kill " . $campaign->pid;
                    exec($command);
                    continue;
                }
            }
            
            # Verifying if has previous
            if ($cache->hasItem($campaignKey . '_previous')) {
                # Getting previous
                $previous = json_decode($cache->getItem($campaignKey . '_previous'),true);
                
                # Verifying if is modified
                if ($data == $previous) {
                    # Verifying if repeated was set and increase counter
                    if (!isset($repeated[$campaignKey])) {
                        $repeated[$campaignKey] = 0;
                    }
                    $repeated[$campaignKey]++;
                    
                    # Verifying if data was repeated at max loops
                    if ($repeated[$campaignKey] > $max_repeats) {
                        # Updating campaign statuses
                        $campaign->sent = $data['sent'];
                        $campaign->fail = $data['fail'];
                        $campaign->progress = $data['progress'];
                        $campaign->pid = null;
                        
                        $campaign->save();
                        
                        # Removing emails from queue
                        foreach ($data['success'] as $index => $email) {
                            if ($collection->remove($campaignKey,$email)) {
                                unset($data['success'][$index]);
                            }
                        }
                        
                        # Cleaning cache
                        $cache->removeItem($campaignKey);
                        $cache->removeItem($campaignKey . "_previous");
                    }
                } else {
                    # Set previous cache
                    $cache->setItem($campaignKey . "_previous", json_encode($data));
                    
                    # Restarting counter of repeated cache
                    $repeated[$campaignKey] = 0;
                }
            } else {
                # Set previous cache
                $cache->setItem($campaignKey . "_previous", json_encode($data));
            }
        } else {
            # Verifying if process is started and not running yet
            if ($campaign->status == Campaign::STATUS_START && is_null($campaign->pid)) {
                switch (pcntl_fork()) {
                    case 0 :
                        $args = array($campaignKey);
                        pcntl_exec(dirname(__FILE__) . "/../bin/m4a1",$args);
                        exit(0);
                    default :
                        break;
                }
            }
        }
    }

    # Waiting...
    sleep(5);
}