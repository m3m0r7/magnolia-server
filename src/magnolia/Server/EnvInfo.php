<?php
namespace Magnolia\Server;

use Magnolia\Client\ClientInterface;
use Magnolia\Exception\ServerInterruptException;
use Monolog\Logger;

final class EnvInfo extends AbstractServer implements ServerInterface
{
    protected $loggerChannelName = 'EnvInfo';
    protected $loggerLevel = Logger::DEBUG;

    public function run(): void
    {
        $host = getenv('ENV_INFO_LISTEN_HOST');
        $port = getenv('ENV_INFO_LISTEN_PORT');
        $this->logger->info(
            'Started EnvInfo receiving server.',
            [
                "$host:$port",
            ]
        );

        while (true) {
            $this->logger->info('Instantiate a server');
            $server = stream_socket_server(
                sprintf(
                    'tcp://%s:%d',
                    $host,
                    $port,
                )
            );

            if ($server === false) {
                throw new ServerInterruptException('Server cannot wakeup.');
            }

            while (true) {
                $this->logger->info('Listening started.');
                while ($client = @stream_socket_accept($server)) {
                    (new \Magnolia\Client\EnvInfo($client))
                        ->start();
                }
            }
        }
    }
}
