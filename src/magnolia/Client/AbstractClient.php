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
    protected $procedures = [];

    /**
     * @var Stream $client
     */
    protected $client;

    public function __construct(
        Stream $client,
        array &$channels = [],
        array &$synchronizers = [],
        array &$procedures = []
    ) {
        $this->client = $client;
        $this->channels = $channels;
        $this->synchronizers = $synchronizers;
        $this->procedures = $procedures;
        $this->logger = Functions::getLogger(
            $this->loggerChannelName,
            $this->loggerLevel,
        );

        $this->logger->info(
            'Connected client',
            [$this->client->getPeer()]
        );
    }

    public function getProcedures(): array
    {
        return $this->procedures;
    }

    abstract public function start(): void;
}
