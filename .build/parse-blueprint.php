#!/usr/bin/php
<?php

# Setting root path
$root_path = dirname(__FILE__) . '/../';

# Reading configuration file
$config = parse_ini_file($root_path . 'app/config/application.ini',true);

# Getting content of blueprint file
$blueprint_content = file_get_contents($root_path . 'blueprint.apib');
$blueprint_content = str_replace("http://domain.com/api/",$config['general']['base_url'] . "api/",$blueprint_content);

# Opening file
$handle = fopen($root_path . 'blueprint.apib',"w");
fwrite($handle,$blueprint_content);
fclose($handle);