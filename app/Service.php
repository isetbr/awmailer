<?php

# Getting autoload from Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/App.php';

# Loading resources
use Iset\Model\Campaign;

# Initializing Service
define("PROCESS_TITLE",'m4a1');
@cli_set_process_title(PROCESS_TITLE);

# Initializing Application
$app = App::configure();

# Initializing cache component
/*$cache = Zend\Cache\StorageFactory::factory($app['config']['cache']['zendcache']);
$cache->setOptions(array('cache_dir'=>$app['cache_path']));*/

# Initializing Campaign table
$campaignTable = new Iset\Model\CampaignTable($app);
$queueCollection = new Iset\Model\QueueCollection($app);

# Initializing Campaign
if (isset($_SERVER['argv'][1])) {
    $campaignKey = $_SERVER['argv'][1];
} else {
    # Campaign not found
    die();
}
$campaign = $campaignTable->getCampaignByKey($campaignKey);

# Validaint campaign 
if ($campaign) {
    # Verifying if campaign was runing
    if (!is_null($campaign->pid) && posix_getpgid((int)$campaign->pid) != false) {
        # Campaign process already running in proccess
        die();
    }
    
    # Verify if campaign is in cache
    if ($app['cache']->hasItem($campaignKey)) {
        # Error: Campaign in cache
        die();
    }
} else {
    # Error: Campaign not found
    die();
}

# Getting queue
$queue = $queueCollection->fetch($campaignKey);

# Verifying if has emails to queue
if (count($queue) == 0) {
    # Error: You don't have emails to queue
    die();
}

# Forking process 
$pid = pcntl_fork();
if ($pid) { exit(); }

# Getting PID from child process
$pid = getmypid();

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
    # Fail to start campaign cache 
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

# Setting flag to finish process
$campaignCache['done'] = 1;

# Writing in cache
$app['cache']->setItem($campaignKey, json_encode($campaignCache));