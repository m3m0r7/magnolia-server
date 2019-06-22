<?php
namespace Magnolia\Server;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

abstract class AbstractServer
{
    protected $loggerChannelName = null;
    protected $logger;

    public function __construct()
    {
        $this->logger = new Logger($this->loggerChannelName);
        $this->logger->pushHandler(
            new StreamHandler(
                getenv('STDOUT'),
                Logger::INFO
            )
        );
    }

    abstract public function run(): void;
}
