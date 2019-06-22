<?php
namespace Magnolia\Client;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Magnolia\Utility\Functions;

abstract class AbstractClient
{
    protected $loggerChannelName = null;
    protected $logger;
    protected $loggerLevel = Logger::INFO;
    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
        $this->logger = Functions::getLogger(
            $this->loggerChannelName,
            $this->loggerLevel,
        );
    }
}
