<?php


use NwApi\Di;
use NwApi\Middlewares\JsonApiMiddleware as JsonApiMiddleware;

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
    // Force slim debug mode to false to handle Json response
    'debug' => false,
    'templates.path' => $di->templatesPath,
]);
$app->add(new JsonApiMiddleware());
if ($debug === true) {
    $app->add(new \Slim\Middleware\PrettyExceptions());
    $app->error(function (\Exception $e) {
        throw $e;
    });
}

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

return $app;
