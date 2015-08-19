<?php

$slimMode = getenv('SLIM_MODE');
if ($slimMode === 'development') {
    $slimMode = 'development';
    ini_set('display_errors', 1);
    $debug = true;
} else {
    $slimMode = 'production';
    ini_set('display_errors', 0);
    $debug = false;
}
ini_set('error_reporting', E_ALL);

$app = new \Slim\Slim([
    'mode' => $slimMode,
    'debug' => $debug,
]);

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

return $app;
