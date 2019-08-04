<?php
require __DIR__ . '/../kernel.php';

try {
    touch(sys_get_temp_dir() . '/camera.jpg');

    (new \Magnolia\Main())
        ->register(\Magnolia\Server\CameraReceiver::class)
        ->register(\Magnolia\Server\CameraDistributor::class)
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