#!/usr/bin/env php
<?php
/**
 * Wait for mysql host to be up
 */
require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';
$mysqlConfig = NwApi\Libraries\Config::getInstance('mysql');
$retry = 0;
do {
    $mysqli = new mysqli($mysqlConfig->host, $mysqlConfig->user, $mysqlConfig->password);
    $retry ++;
    if (!is_null($mysqli->connect_error)) {
        echo "Waiting for mysql to be up (retry:" . $retry . ")\n";
        sleep(1);
    }
} while ($retry < 20 && !is_null($mysqli->connect_error));

if (!is_null($mysqli->connect_error)) {
    echo "Connection error: " . $mysqli->connect_errno . " "
    . $mysqli->connect_error . "\n";
    exit(1);
}
echo "Mysql host up\n";
