<?php
namespace Magnolia\Client;

use Monolog\Logger;

final class EnvInfo extends AbstractClient implements ClientInterface
{
    protected $loggerChannelName = 'EnvInfo.Client';

    public function start(): void
    {
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
