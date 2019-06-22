<?php
require __DIR__ . '/vendor/autoload.php';

$env = Dotenv\Dotenv::create(__DIR__);
$env->load();

(new \Magnolia\Main([
    \Magnolia\Server\Camera::class,
    \Magnolia\Server\EnvInfo::class,
]))->run();
