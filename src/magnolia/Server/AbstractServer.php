<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Magnolia\Utility\Functions;

abstract class AbstractServer implements ServerInterface
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
    }

    abstract public function run(): void;

    public function getServerName(): string
    {
        return null;
    }

    public function getListenHost(): string
    {
        return null;
    }

    public function getListenPort(): int
    {
        return null;
    }

    public function getClientClassName()
    {
        return null;
    }
}
