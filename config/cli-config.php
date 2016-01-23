<?php

require dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
use Doctrine\ORM\Tools\Console\ConsoleRunner;

//create database if not exists
$mysqlConfig = NwApi\Libraries\Config::getInstance('mysql');
$mysqli = new mysqli($mysqlConfig->host, $mysqlConfig->user, $mysqlConfig->password, '', $mysqlConfig->port);
if (!$mysqli->query('CREATE DATABASE IF NOT EXISTS '.$mysqli->real_escape_string($mysqlConfig->database))) {
    throw new Exception('Unable to create database');
}

return ConsoleRunner::createHelperSet(NwApi\Di::getInstance()->em);
