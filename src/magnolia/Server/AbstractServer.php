<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Magnolia\Stream\Stream;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Magnolia\Utility\Functions;

abstract class AbstractServer implements ServerInterface
{
    protected $loggerChannelName = 'none';
    protected $logger;
    protected $loggerLevel = Logger::INFO;
    protected $channels = [];
    protected $instantiationClientClassName = null;
    protected $synchronizers = [];
    protected $synchronizeKey = null;
    protected $clientStreamClass = Stream::class;

    public function __construct(array &$channels = [], array &$synchronizers = [])
    {
        $this->channels = $channels;
        $this->synchronizers = $synchronizers;
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
}
