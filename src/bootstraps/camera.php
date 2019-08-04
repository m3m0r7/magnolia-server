<?php
require __DIR__ . '/base.php';

try {
    (new \Magnolia\Main())
        ->register(\Magnolia\Server\StreamingPipeline::class)
        ->register(\Magnolia\Server\Camera::class)
        ->run();

} catch (\Exception | Error $e) {
    error($e);
}