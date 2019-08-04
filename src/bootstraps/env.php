<?php
require __DIR__ . '/base.php';

try {
    (new \Magnolia\Main())
        ->register(\Magnolia\Server\EnvInfo::class)
        ->run();

} catch (\Exception | Error $e) {
    error($e);
}