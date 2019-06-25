<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Stream\Stream;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Magnolia\Utility\Functions;

abstract class AbstractClient implements ClientInterface
{
    protected $loggerChannelName = null;
    protected $logger;
    protected $loggerLevel = Logger::INFO;
    protected $data = [];
    protected $channels = [];
    protected $synchronizers = [];

    /**
     * @var Stream $client
     */
    protected $client;

    public function __construct(Stream $client, array &$channels = [], array &$synchronizers = [])
    {
        $this->client = $client;
        $this->channels = $channels;
        $this->synchronizers = $synchronizers;
        $this->logger = Functions::getLogger(
            $this->loggerChannelName,
            $this->loggerLevel,
        );

        $this->logger->info(
            'Connected client',
            [$this->client->getPeer()]
        );
    }

    abstract public function start(): void;
}
