<?php

# Getting autoload from Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/App.php';

# Helpers
# Print a line in terminal
function print_ln ($text = null, $break = true) { 
    echo $text . (($break) ? PHP_EOL : ''); 
}
# Separator
function separate() { 
    echo "##########################################################" . PHP_EOL; 
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

# Loading resources
use Iset\Model\Campaign;
use Iset\Model\CampaignTable;
use Iset\Model\QueueCollection;

# Initialzing Silex Application
$app = App::configure();

# Initializing cache component
$cache = Zend\Cache\StorageFactory::factory(array(
    'adapter'=>'filesystem',
    'plugins'=>array(
        'exception_handler' => array('throw_exceptions'=>false),
    ),
));
$cache->setOptions(array('cache_dir'=>$app['cache_path']));

# Initializing gateway
$gateway = new CampaignTable($app);
$collection = new QueueCollection($app);

# Forking process 
/*$pid = pcntl_fork();
if ($pid) { exit(); }*/

# Initializing control vars
$max_repeats = 10;
$repeated = array();

# Starting daemon
while (true) {
    # Getting campaigns
    $campaignsActive = $gateway->getCampaignsByStatus(Campaign::STATUS_START);
    $campaignsPaused = $gateway->getCampaignsByStatus(Campaign::STATUS_PAUSE);
    $campaigns = array_merge($campaignsActive,$campaignsPaused);
    
    # Loop into results
    foreach ($campaigns as $campaign) {
        $campaignKey = $campaign->getCampaignKey();
        # Verifying if campaign is in cache
        $result = $cache->hasItem($campaignKey);
        if ($result) {
            # Getting data from cache
            $data = json_decode($cache->getItem($campaignKey),true);
            
            # Control var
            $update = false;
            
            # Verifying if has previous
            if ($cache->hasItem($campaignKey . '_previous')) {
                # Getting previous
                $previous = json_decode($cache->getItem($campaignKey . '_previous'),true);
                
                # Verifying if is modified
                $update = ($data != $previous) ? true : false;
            } else {
                $update = true;
            }
            
            # Verify to update
            if ($update) {
                # Locking process
                lock($campaignKey);
                
                # Updating campaign statuses
                $campaign->sent = $data['sent'];
                $campaign->fail = $data['fail'];
                $campaign->progress = $data['progress'];
                $campaign->save();
                
                # Removing emails from queue
                foreach ($data['success'] as $index => $email) {
                    if ($collection->remove($campaignKey,$email)) {
                        unset($data['success'][$index]);
                    }
                }
                
                # Writing in cache
                $cache->setItem($campaignKey, json_encode($data));
                $cache->setItem($campaignKey . "_previous", json_encode($data));
                
                # Unlocking file
                unlock($campaignKey);
                print_ln("Campaign " . $campaignKey);
                var_dump($data);
                print_ln();
                separate();
            } else {
                if (!isset($repeated[$campaignKey])) {
                    $repeated[$campaignKey] = 0;
                }
                $repeated[$campaignKey]++;
                
                if ($repeated[$campaignKey] > $max_repeats) {
                    $cache->removeItem($campaignKey);
                    $cache->removeItem($campaignKey . "_previous");
                }
                
                print_ln("Campaign " . $campaignKey);
                print_ln("REPEATED");
                print_ln();
                separate();
            }
        }
    }
    
    print_ln("OK!");
    
    sleep(3);
}