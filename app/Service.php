<?php

# Getting autoload from Composer
require_once dirname(__FILE__) . '/../vendor/autoload.php';
require_once dirname(__FILE__) . '/../app/App.php';

# Loading resources
use Iset\Api\Resource\Campaign;
use Iset\Model\CampaignTable;
use Iset\Model\QueueCollection;

# Initializing Application
$app = App::configure();
$app->boot();

# Setting error handler
set_error_handler(function ($code, $message, $file, $line) use ($app) {
    $app['monolog.service']->addError('Internal error occurred', array('code'=>$code,'message'=>$message,'file'=>$file,'line'=>$line));
});

# Initializing Campaign table
$campaignTable = new CampaignTable($app);
$queueCollection = new QueueCollection($app);

# Initializing Campaign
if (isset($_SERVER['argv'][1])) {
    $campaignKey = trim(str_replace('> /dev/null','',$_SERVER['argv'][1]));
} else {
    # Campaign key is not set
    die();
}

# Logging
$app['monolog.service']->addInfo('Starting service',array('campaign'=>$campaignKey));

# Getting campaign
$campaign = $campaignTable->getCampaignByKey($campaignKey);

# Validaint campaign
if ($campaign) {
    # Verifying if campaign was runing
    if (!is_null($campaign->pid) && posix_getpgid((int) $campaign->pid) != false) {
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

# Initializing logger for process
$app['monolog.process'] = $app->share(function () use ($app,$campaignKey) {
    # Creating stream
    $stream = $app['log_path'] . 'processes/' . $campaignKey . '.log';
    $handler = new Monolog\Handler\StreamHandler($stream,Monolog\Logger::DEBUG,true,0777);

    # Initializing logger
    $logger = new Monolog\Logger('process',array($handler));

    # Setting stream in service logger
    $app['monolog.service']->pushHandler($handler);

    return $logger;
});
$app['monolog.service']->addInfo('Initialized logger for process');

# Forking process
$pid = pcntl_fork();
if ($pid) {
    //pcntl_wait($status);
    exit();
}

# Setting session
$sess_id = posix_setsid();

# Configuring session
posix_seteuid(1001);
posix_setegid(1001);

# Getting PID from child process
$pid = posix_getpid();

# Logging
$app['monolog.service']->addInfo('Service successfully started',array('campaign'=>$campaignKey,'PID'=>$pid));

# Setting PID on campaign
$campaign->pid = $pid;
$campaignTable->assertGatewayConnection();
$campaignTable->saveCampaign($campaign);

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
$app['monolog.process']->addInfo('Preparing campaign vars');

# Initializing control vars
# Progress
$app['monolog.process']->addInfo('Calculated progress factor');

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

        # Verifying result and increasing counter
        if ($result) {
            $app['monolog.process']->addInfo('Email sent',array('destination'=>$destination_email));
            $campaignCache['sent']++;
            $campaignCache['success'][] = $destination_email;
        } else {
            $app['monolog.process']->addInfo('Email sent error',array('destination'=>$destination_email));
            $campaignCache['fail']++;
            $campaignCache['errors'][] = $destination_email;
        }

        # Increasing progress
        $campaignCache['progress'] = floor((($campaignCache['sent'] + $campaignCache['fail'])* 100) / $campaignCache['total']);

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

exit();
