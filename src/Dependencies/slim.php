<?php

use NwApi\Libraries\EntitiesRouter;
use NwApi\Controllers\RestEntities as RestController;
use NwApi\Di;

$di = Di::getInstance();
if ($di->env === ENV_DEVELOPMENT) {
    $slimMode = 'development';
    $debug = true;
} else {
    $slimMode = 'production';
    $debug = false;
}

$app = new Slim\Slim([
    'mode' => $slimMode,
    'debug' => $debug,
        ]);

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

$app = EntitiesRouter::getInstance()->addRoutes($app, RestController::getInstance());

return $app;
