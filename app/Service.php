<?php

# Getting autoload from Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/App.php';

# Loading resources
use Iset\Api\Callback;
use Iset\Api\Resource\Campaign;
use Iset\Model\CampaignTable;
use Iset\Model\QueueCollection;

# Initializing Application
$app = App::configure();

# Initializing Service
define("PROCESS_TITLE",$app['config']['service']['name']);

# Initializing Campaign table
$campaignTable = new CampaignTable($app);
$queueCollection = new QueueCollection($app);

# Initializing Campaign
if (isset($_SERVER['argv'][1])) {
    $campaignKey = trim(str_replace('> /dev/null','',$_SERVER['argv'][1]));
} else {
    # Campaign not found
    die();
}

# Logging
$app['monolog.service']->addInfo('Starting service',array('campaign'=>$campaignKey));

# Getting campaign
$campaign = $campaignTable->getCampaignByKey($campaignKey);

# Validaint campaign 
if ($campaign) {
    # Verifying if campaign was runing
    if (!is_null($campaign->pid) && posix_getpgid((int)$campaign->pid) != false) {
        $app['monolog.service']->addError('Service stopped',array('campaign'=>$campaignKey,'reason'=>'Service already running'));
        die();
    }
    
    # Verify if campaign is in cache
    if ($app['cache']->hasItem($campaignKey)) {
        $app['monolog.service']->addError('Service stopped',array('campaign'=>$campaignKey,'reason'=>'Campaign in cache'));
        die();
    }
} else {
    $app['monolog.service']->addError('Service stopped',array('campaign'=>$campaignKey,'reason'=>'Campaign not found'));
    die();
}

# Verifying if has emails in queue
if (!$queueCollection->hasQueue($campaignKey)) {
    $app['monolog.service']->addError('Service stopped',array('campaign'=>$campaignKey,'reason'=>'Empty queue'));
    die();
}

# Forking process 
$pid = pcntl_fork();
if ($pid) { exit(); }

# Re-init application
$app['db']->close();
$app['db']->connect();

# Getting PID from child process
$pid = getmypid();

# Logging
$app['monolog.service']->addInfo('Service successfully started',array('campaign'=>$campaignKey,'PID'=>$pid));

# Setting PID on campaign
$campaign->pid = $pid;
$campaign->save();

# Initializing campaign cache
$campaignCache = array(
    'total'=>$campaign->total,
    'sent'=>$campaign->sent,
    'fail'=>$campaign->fail,
    'progress'=>$campaign->progress,
    'pid'=>$campaign->pid,
    'status'=>$campaign->status,
    'success'=>array(),
    'errors'=>array(),
);

# Writing campaign in cache
$result = $app['cache']->setItem($campaignKey, json_encode($campaignCache));
if (!$result) {
    $app['monolog.service']->addError('Service crashed',array('campaign'=>$campaignKey,'reason'=>'Cannot initialize campaign cache')); 
    die();
}

# Preparing content
$subject = $campaign->subject;
$message = $campaign->body;
$headers = $campaign->headers;

# Initializing control vars
# Progress
$factor = ceil((($campaign->total - ($campaign->sent + $campaign->fail)) / 100));
$counter = 0;

# Queue package size
$max_package_size = $app['config']['service']['queue']['max_package_size'];
$skip  = 0;

# Getting package
while (count($queue = $queueCollection->fetch($campaignKey,null,$max_package_size,$skip)) > 0) {
    # Starting queue
    foreach ($queue as $row) {
        # Verifying if is a custom queue
        if ($campaign->user_vars == 1  || $campaign->user_headers == 1) {
            # Getting destination email
            $destination_email = $row['email'];
            $parsed_body = $message;
    
            # Verifying if has vars to parse body
            if ($campaign->user_vars == 1 && is_array($row['vars'])) {
                foreach ($row['vars'] as $key => $value) {
                    $parsed_body = str_replace('%%' . $key . '%%', $value, $parsed_body);
                }
            }
    
            # Verifying if has custom headers
            if ($campaign->user_headers == 1 && is_array($row['headers'])) {
                # Merge campaign headers with user headers
                $parsed_headers = array_merge($headers,$row['headers']);
            }
        } else {
            # Simple queue
            $destination_email = $row;
            $parsed_body = $message;
            $parsed_headers = $headers;
        }
    
        # Loop into headers to parse the string
        $temporary_headers = array();
        foreach ($parsed_headers as $header_key => $header_value) {
            $temporary_headers[] = $header_key . ': ' .$header_value;
        }
        $parsed_headers = implode("\r\n",$temporary_headers);
    
        # Sending mail
        $result = mail($destination_email,$subject,$parsed_body,$parsed_headers);

        # Increasing counter
        $counter++;

        # Verifying result and increasing counter
        if ($result) {
            $campaignCache['sent']++;
            $campaignCache['success'][] = $destination_email;
        } else {
            $campaignCache['fail']++;
            $campaignCache['errors'][] = $destination_email;
        }
    
        # Verifying if counter
        if ($counter == $factor) {
            $counter = 0;
            $campaignCache['progress']++;
        }
    
        # Writing in cache
        $app['cache']->setItem($campaignKey, json_encode($campaignCache));
    }
    
    # Increasing skip
    $skip = $skip + $max_package_size;
}

# Setting flag to finish process
$campaignCache['done'] = 1;

# Writing in cache
$app['cache']->setItem($campaignKey, json_encode($campaignCache));

# Logging
$app['monolog.service']->addInfo('Service finished',array('campaign'=>$campaignKey));