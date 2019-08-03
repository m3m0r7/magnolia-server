<?php
require __DIR__ . '/vendor/autoload.php';

$env = Dotenv\Dotenv::create(__DIR__);
$env->load();

define('ROOT_DIR', __DIR__);
define('STORAGE_DIR', ROOT_DIR . '/storage');

date_default_timezone_set(getenv('TIMEZONE'));
