<?php
namespace Magnolia\Timer;

use Magnolia\Contract\TimerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Magnolia\Utility\Functions;

abstract class AbstractTimer implements TimerInterface
{
    protected $loggerChannelName = 'none';
    protected $logger;
    protected $loggerLevel = Logger::INFO;
    protected $channels = [];

    public function __construct(array &$channels = [])
    {
        $this->channels = $channels;
        $this->logger = Functions::getLogger(
            $this->loggerChannelName,
            $this->loggerLevel,
        );

        $this->logger->info('Timer is running.');
    }


    abstract static public function getIntervalTime(): int;
}