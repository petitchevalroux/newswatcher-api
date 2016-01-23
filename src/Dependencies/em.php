<?php

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$mysqlConfig = NwApi\Libraries\Config::getInstance('mysql');
$di = NwApi\Di::getInstance();
$isDevMode = true;

$annotationConfig = Setup::createAnnotationMetadataConfiguration(
    [$di->entitiesPath],
    $isDevMode,
    null,
    null,
    false // Do not use simple annotation reader
);

if ($di->env === ENV_DEVELOPMENT) {
    $annotationConfig->setSQLLogger($di->sqlLogger);
}

return EntityManager::create([
    'driver' => 'mysqli',
    'user' => $mysqlConfig->user,
    'password' => $mysqlConfig->password,
    'host' => $mysqlConfig->host,
    'port' => $mysqlConfig->port,
    'dbname' => $mysqlConfig->database,
    'charset' => 'utf8mb4',
], $annotationConfig);
