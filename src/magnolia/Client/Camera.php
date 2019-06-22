<?php
namespace Magnolia\Client;

use Monolog\Logger;

final class Camera extends AbstractClient implements ClientInterface
{
    protected $loggerChannelName = 'Camera.Client';

    public function start(): void
    {
        $this->logger->info('Connected client', [
            stream_socket_get_name(
                $this->client->getResource(),
                true
            )
        ]);
    }
}
