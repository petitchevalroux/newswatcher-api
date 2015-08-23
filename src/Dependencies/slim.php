<?php

use NwApi\Libraries\EntitiesRouter;
use NwApi\Controllers\RestEntities as RestController;
use NwApi\Di;
use NwApi\Middlewares\Json as JsonMiddleware;

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
    'templates.path' => $di->templatesPath,
]);
$app->add(new JsonMiddleware());
$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

$app = EntitiesRouter::getInstance()->addRoutes($app, RestController::getInstance());

return $app;
