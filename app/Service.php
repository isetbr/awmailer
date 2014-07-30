<?php

# Getting autoload from Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/App.php';

# Loading resources
use Iset\Model\Campaign;

# Helpers
# Print a line in terminal
function print_ln ($text = null, $break = true) { 
    #echo $text . (($break) ? PHP_EOL : ''); 
}
# Separator
function separate() { 
    #echo "##########################################################" . PHP_EOL; 
}
# Lock campaign
function lock($key) {
    global $app;
    $handle = fopen($app['cache_path'] . $key.".lock", "w");
    fwrite($handle, time());
    fclose($handle);
}
# Unlock campaign
function unlock($key) {
    global $app;
    if (file_exists($app['cache_path'] . $key . ".lock")) {
        unlink($app['cache_path'] . $key . ".lock");
    }
}
# IsLocked campaign
function is_locked($key) {
    global $app;
    return file_exists($app['cache_path'] . $key . ".lock");
}

# Welcome
separate();
print_ln(PHP_EOL . "Welcome to M4A1 Mailer Service" . PHP_EOL);
print_ln("Please wait, initializing service..." . PHP_EOL);
separate();

# Initializing Daemon
print_ln(PHP_EOL . "Defining process title... ",false);
define("PROCESS_TITLE",'m4a1');
//setproctitle(PROCESS_TITLE);
//setthreadtitle(PROCESS_TITLE);
print_ln("OK!");

print_ln("Initializing components...");

# Initializing Application
print_ln("=> Silex Application... ",false);
$app = App::configure();
print_ln("OK!");

# Initializing cache component
print_ln("=> Zend Cache... ",false);
$cache = Zend\Cache\StorageFactory::factory(array(
    'adapter'=>'filesystem',
    'plugins'=>array(
       'exception_handler' => array('throw_exceptions'=>false),
    ),
));
$cache->setOptions(array('cache_dir'=>$app['cache_path']));
print_ln("OK!");

print_ln("=> Collection and table gateways... ",false);
# Initializing Campaign table
$campaignTable = new Iset\Model\CampaignTable($app);
$queueCollection = new Iset\Model\QueueCollection($app);
print_ln("OK!");

print_ln("The service is configured!" . PHP_EOL);
separate();

print_ln(PHP_EOL . "Retrieving campaign data... ",false);
# Initializing Campaign
if (isset($_SERVER['argv'][1])) {
    $campaignKey = $_SERVER['argv'][1];
} else {
    print_ln(PHP_EOL . "Error: Campaign key not specified.");
    die();
}
$campaign = $campaignTable->getCampaignByKey($campaignKey);
print_ln("OK!");

print_ln("Validating campaing... ",false);
# Validaint campaign 
if ($campaign) {
    # Verifying if campaign was runing
    if (!is_null($campaign->pid) && posix_getpgid((int)$campaign->pid) != false) {
        print_ln(PHP_EOL . "Error: Campaign process already running in proccess " . $campaign->pid);
        die();
    }
    
    # Verify if campaign is in cache
    if ($cache->hasItem($campaignKey)) {
        print_ln(PHP_EOL . "Error: Campaign in cache, verify current status");
        die();
    }
} else {
    print_ln(PHP_EOL . "Error: Campaign not found");
    die();
}
print_ln("OK!");

print_ln("Getting queue from campaign... ",false);
# Getting queue
$queue = $queueCollection->fetch($campaignKey);

# Verifying if has emails to queue
if (count($queue) == 0) {
    print_ln("Error: You don't have emails to queue");
    die();
}
print_ln("OK!" . PHP_EOL);
separate();

print_ln(PHP_EOL . "Forking child process... ",false);
# Forking process 
$pid = pcntl_fork();
if ($pid) { exit(); }

# Getting PID from child process
$pid = getmypid();

print_ln("Forked!!");
print_ln("Starting process as PID " . $pid);
print_ln("FIRE!!");

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
    'success'=>array(),
    'errors'=>array(),
);

# Writing campaign in cache
$result = $cache->setItem($campaignKey, json_encode($campaignCache));
if (!$result) {
    die();
}

# Preparing content
$subject = $campaign->subject;
$message = $campaign->body;
$headers = implode("\r\n", $campaign->headers);

# Initializing control vars
# Progress
$factor = ceil((($campaign->total - ($campaign->sent + $campaign->fail)) / 100));
$counter = 0;

# Starting queue
foreach ($queue as $email) {
    # Sending mail
    $result = mail($email,$subject,$message,$headers);
    //$result = true;
    $counter++;
    
    while (is_locked($campaignKey)) {
        sleep(1);
    }
    
    # Getting campaign cache
    $campaignCache = json_decode($cache->getItem($campaignKey),true);
    
    # Verifying result and increasing counter
    if ($result) {
        $campaignCache['sent']++;
        $campaignCache['success'][] = $email;
    } else {
        $campaignCache['fail']++;
        $campaignCache['errors'][] = $email;
    }
    
    # Verifying if counter 
    if ($counter == $factor) {
        $counter = 0;
        $campaignCache['progress']++;
    }
    
    # Writing in cache
    $cache->setItem($campaignKey, json_encode($campaignCache));
}

$campaign->pid = null;
$campaign->status = Campaign::STATUS_DONE;
$campaign->save();