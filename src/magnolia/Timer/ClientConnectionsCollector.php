<?php
namespace Magnolia\Timer;

use Magnolia\Contract\TimerInterface;

final class ClientConnectionsCollector extends AbstractTimer implements TimerInterface
{
    protected $loggerChannelName = 'ClientConnectionsCollector';

    public static function getIntervalTime(): int
    {
        return 5000;
    }

    public function run(): void
    {
        $this->logger->info('Collect clients.');
    }
}