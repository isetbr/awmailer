<?php

# CONFIGURATION FOR PHP INTEGRATED WEBSERVER
$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

# Importing application
require_once __DIR__ . '/../app/App.php';

# Initializing
$app = App::configure();

# RUN!
$app->run();