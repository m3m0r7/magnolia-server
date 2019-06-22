<?php
namespace Magnolia\Client;

use Magnolia\Stream\Stream;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Magnolia\Utility\Functions;

abstract class AbstractClient
{
    protected $loggerChannelName = null;
    protected $logger;
    protected $loggerLevel = Logger::INFO;

    /**
     * @var Stream $client
     */
    protected $client;

    public function __construct(Stream $client)
    {
        $this->client = $client;

        $this->logger = Functions::getLogger(
            $this->loggerChannelName,
            $this->loggerLevel,
        );

        $this->logger->info(
            'Connected client',
            [
                stream_socket_get_name(
                    $this->client->getResource(),
                    true
                )
            ]
        );
    }
}
