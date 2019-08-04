<?php

require __DIR__ . '/../vendor/autoload.php';

define('ROOT_DIR', __DIR__ . '/../');
define('STORAGE_DIR', ROOT_DIR . '/storage');

$env = Dotenv\Dotenv::create(ROOT_DIR);
$env->load();


date_default_timezone_set(getenv('TIMEZONE'));

/**
 * @param Exception|Error $e
 */
function error($e)
{
    // Caught Fatal error
    $errstr = $e->getMessage();
    $errline = $e->getLine();
    $errfile = $e->getFile();

    printf("\e[35m$errstr\e[39m\n");
    printf("\e[35m  Line: $errline\e[39m\n");
    printf("\e[35m  File: $errfile\e[39m\n");
}