<?php
require __DIR__ . '/base.php';

try {
    (new \Magnolia\Main())
        ->register(\Magnolia\Server\API\Api::class)
        ->run();

} catch (\Exception | Error $e) {
    error($e);
}