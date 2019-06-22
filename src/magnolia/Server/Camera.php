<?php
namespace Magnolia\Server;

final class Camera extends AbstractServer implements ServerInterface
{
    protected $loggerChannelName = 'Camera';

    public function run(): void
    {
        $this->logger->info('Started camera receiving server.');
        while (true) {
            $this->logger->info('From camera');
            sleep(mt_rand(1, 3));
        }
    }
}

