#!/usr/bin/php
<?php

echo "reading configuration files..." . PHP_EOL;
# Loading application.ini file
$config = parse_ini_file(dirname(__FILE__) . '/../app/config/application.ini',true);

echo "initializing mysql database..." . PHP_EOL;
# Getting mysql connection params
$mysql_host   = $config['database']['mysql.host'];
$mysql_user   = $config['database']['mysql.user'];
$mysql_pass   = $config['database']['mysql.password'];
$mysql_dbname = $config['database']['mysql.dbname'];

# Establishing connection with database
$conn = mysql_connect($mysql_host,$mysql_user,$mysql_pass);
if (!$conn) {
    echo "ERROR: Cannot connect to MySQL database!" . PHP_EOL;
    exit(1);
}

if (!mysql_select_db($mysql_dbname)) {
    echo "ERROR: Cannot use the selected database!" . PHP_EOL;
}

# Importing dump
echo "creating tables..." . PHP_EOL;
$output = "";
$command = "mysql -h " . $mysql_host . " -u " . $mysql_user . " -p" . $mysql_pass . " " . $mysql_dbname . " < db/database.sql > /dev/null 2>&1";
exec($command,$output);

echo "testing tables..." . PHP_EOL;
mysql_query("INSERT INTO `ipaddress` (`ipaddress`) VALUES ('127.0.0.1');");

# Getting mongodb connection params
$mongo_host = $config['database']['mongo.dsn'];
$mongo_dbname = $config['database']['mongo.dbname'];

# MongoDB
echo "initializing mongodb databases..." . PHP_EOL;
$mongoclient = new MongoClient($mongo_host);
$mongo_db = $mongoclient->selectDB($mongo_dbname);
$mongo_db->createCollection('mail_queue');

echo "done!" . PHP_EOL;