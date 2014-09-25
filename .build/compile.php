#!/usr/bin/php
<?php

$root_path = realpath(dirname(__FILE__) . '/../');
$bin_path = $root_path . '/bin/';
$daemon = $bin_path . "awd";
$service = $bin_path . "awmailer";

if (file_exists($daemon)) {
    unlink($daemon);
}

$content = <<<EOF
#!/bin/bash
php -q -c $root_path/app/config/php-cli.ini $root_path/app/Daemon.php
EOF;
$handle = fopen($daemon,"w");
fwrite($handle,$content);
fclose($handle);

if (file_exists($service)) {
    unlink($service);
}

$content = <<<EOF
#!/bin/bash
php -q -c $root_path/app/config/php-cli.ini $root_path/app/Service.php
EOF;
$handle = fopen($service,"w");
fwrite($handle,$content);
fclose($handle);