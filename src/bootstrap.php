<?php

require __DIR__ . '/vendor/autoload.php';

$env = Dotenv\Dotenv::create(__DIR__);
$env->load();

date_default_timezone_set(getenv('TIMEZONE'));

try {
    (new \Magnolia\Main())
        ->register(\Magnolia\Timer\ClientConnectionsCollector::class)
        ->register(\Magnolia\Server\StreamingPipeline::class)
        ->register(\Magnolia\Server\Camera::class)
        ->register(\Magnolia\Server\EnvInfo::class)
        ->register(\Magnolia\Server\API\EnvInfo::class)
        ->run();

} catch (Exception $e) {
    fwrite(
        fopen('/dev/stderr', 'w'),
        sprintf(
            '%s: %s',
            get_class($e),
            $e->getMessage(),
        )
    );
}
