<?php

// Performing checks
check_php();
check_exec();
check_pcntl();
check_curl();
check_mysql();
check_mongodb();
check_apib();

// Check functions
function check_apib() {
    echo "checking for nodejs..." . PHP_EOL;
    // Verifying NodeJS installation
    $output = '';
    exec('node -v',$output);
    if (preg_match("/v\d.\d+.\d+/",$output[0]) != 1) {
        echo "ERROR: NodeJS is required for generate API docs, check the requirements document." . PHP_EOL;
        exit(1);
    }

    // Checking aglio
    echo "checking for aglio..." . PHP_EOL;
    $output = '';
    exec('aglio > /tmp/aglio_test 2>&1',$output);
    $content = file_get_contents('/tmp/aglio_test',FILE_TEXT);
    if (strstr($content,"Usage") === false) {
        unlink('/tmp/aglio_test');
        echo "ERROR: Aglio is required for generate API docs, check the requirements document." . PHP_EOL;
        exit(1);
    } else {
        unlink('/tmp/aglio_test');
    }

    return true;
}
function check_curl() {
    echo "checking for curl..." . PHP_EOL;
    if (function_exists("curl_init")) {
        return true;
    } else {
        echo "ERROR: CURL not found, check the requirements document." . PHP_EOL;
        exit(1);
    }
}
function check_mysql() {
    echo "checking for mysql..." . PHP_EOL;
    if (class_exists("PDO")) {
        $drivers = PDO::getAvailableDrivers();
        if (in_array('mysql',$drivers)) {
            return true;
        } else {
            echo "ERROR: PDO mySQL driver not found, check the requirements document." . PHP_EOL;
            exit(1);
        }
    } else {
        echo "ERROR: PDO not found,  check the requirements document." . PHP_EOL;
        exit(1);
    }
}
function check_mongodb() {
    echo "checking for mongodb..." . PHP_EOL;
    if (class_exists("MongoClient")) {
        return true;
    } else {
        echo "ERROR: MongoDB not found, check the requirements document." . PHP_EOL;
        exit(1);
    }
}
function check_php() {
    echo "checking for php version..." . PHP_EOL;
    if (version_compare(PHP_VERSION,'5.4.0','>')) {
        return true;
    } else {
        echo "ERROR: AwMailer requires PHP 5.4 or later." . PHP_EOL;
        exit(1);
    }
}
function check_pcntl() {
    echo "checking for pcntl extension..." . PHP_EOL;
    if (function_exists('pcntl_fork')) {
        return true;
    } else {
        echo "ERROR: PCNTL extension not found, check the requirements document." . PHP_EOL;
        exit(1);
    }
}
function check_exec() {
    echo "checking for exec extension..." . PHP_EOL;
    if (function_exists('exec')) {
        return true;
    } else {
        echo "ERROR: exec function is disabled, please enable it to do the installation, you can deactivate after it." . PHP_EOL;
        exit(1);
    }
}