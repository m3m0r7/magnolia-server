<?php
require __DIR__ . '/vendor/autoload.php';

$env = Dotenv\Dotenv::create(__DIR__);
$env->load();

try {
    (new \Magnolia\Main([
        \Magnolia\Server\Camera::class,
        \Magnolia\Server\EnvInfo::class,
    ]))->run();

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
