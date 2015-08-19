<?php

$slimMode = getenv('SLIM_MODE');
if ($slimMode === 'development') {
    $slimMode = 'development';
    ini_set('display_errors', 1);
    ini_set('error_reporting', E_ALL);
    $debug = true;
} else {
    $slimMode = 'production';
    ini_set('display_errors', 0);
    // As reported in https://github.com/slimphp/Slim/issues/1454#issuecomment-132623409
    // Slim stop script execution based on error_reporting settings.
    // It can be dangerous in production
    ini_set('error_reporting', E_ALL & ~E_STRICT & ~E_WARNING & ~E_NOTICE & E_DEPRECATED);
    $debug = false;
}

$app = new \Slim\Slim([
    'mode' => $slimMode,
    'debug' => $debug,
]);

$app->get('/hello/:name', function ($name) {
    echo "Hello, $name";
});

return $app;
