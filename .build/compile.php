#!/usr/bin/php
<?php

# Initializing paths
$root_path = realpath(dirname(__FILE__) . '/../');
$bin_path = $root_path . '/bin/';
$daemon = $bin_path . "awd";
$service = $bin_path . "awmailer";
$php_binary = trim(exec('which php'));

# Removing existing daemon
if (file_exists($daemon)) {
    unlink($daemon);
}

$content = <<<EOF
#!$php_binary -q
<?php require_once("$root_path/app/Daemon.php");
EOF;
$handle = fopen($daemon,"w");
fwrite($handle,$content);
fclose($handle);

# Removing existing service
if (file_exists($service)) {
    unlink($service);
}

$content = <<<EOF
#!$php_binary -q
<?php require_once("$root_path/app/Service.php");
EOF;
$handle = fopen($service,"w");
fwrite($handle,$content);
fclose($handle);