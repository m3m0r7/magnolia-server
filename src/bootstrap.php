<?php

require __DIR__ . '/vendor/autoload.php';

$env = Dotenv\Dotenv::create(__DIR__);
$env->load();

define('ROOT_DIR', __DIR__);
define('STORAGE_DIR', ROOT_DIR . '/storage');

date_default_timezone_set(getenv('TIMEZONE'));

try {
    (new \Magnolia\Main())
        ->register(\Magnolia\Server\StreamingPipeline::class)
        ->register(\Magnolia\Server\Camera::class)
        ->register(\Magnolia\Server\EnvInfo::class)
        ->register(\Magnolia\Server\API\Api::class)
        ->run();

} catch (\Exception | Error $e) {
    // Caught Fatal error
    $errstr = $e->getMessage();
    $errline = $e->getLine();
    $errfile = $e->getFile();

    printf("\e[35m$errstr\e[39m\n");
    printf("\e[35m  Line: $errline\e[39m\n");
    printf("\e[35m  File: $errfile\e[39m\n");
}