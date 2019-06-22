<?php
namespace Magnolia\Server;

final class EnvInfo extends AbstractServer implements ServerInterface
{
    protected $loggerChannelName = 'EnvInfo';

    public function run(): void
    {
        $this->logger->info('Started EnvInfo receiving server.');
        while (true) {
            $this->logger->info('From EnvInfo');
            sleep(mt_rand(1, 3));
        }
    }
}

