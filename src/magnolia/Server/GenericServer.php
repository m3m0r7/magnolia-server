<?php
namespace Magnolia\Server;

use Magnolia\Client\ClientInterface;
use Magnolia\Contract\ServerInterface;
use Magnolia\Exception\ServerInterruptException;
use Magnolia\Stream\Stream;
use Monolog\Logger;

class GenericServer extends AbstractServer implements ServerInterface
{
    /**
     * @throws ServerInterruptException
     */
    public function run(): void
    {
        $clientClassName = $this->getClientClassName();

        while (true) {
            $server = stream_socket_server(
                sprintf(
                    'tcp://%s:%d',
                    $this->getListenHost(),
                    $this->getListenPort(),
                )
            );

            if ($server === false) {
                throw new ServerInterruptException('Failed to start server.');
            }

            $this->logger->info(
                'Server is running.',
                [
                    "{$this->getListenHost()}:{$this->getListenPort()}"
                ]
            );

            /**
             * @var \Swoole\Coroutine\Channel $channel
             */
            $channel = $this->channels[static::class];
            while (true) {
                $this->logger->info('Listening started.');
                while ($client = @stream_socket_accept($server)) {
                    // Check connections,.
                    if ($channel->isFull()) {
                        $this->logger->info(
                            'Failed to connect because it is over MAX_CONNECTIONS.'
                        );
                        continue;
                    }

                    // In one case, Google Chrome send 2 connections (pre-flight and fetching document data).
                    // So, It need asynchronously processing.
                    $clientStream = new Stream($client);
                    $channel->push($clientStream);

                    $connections = $channel->length();
                    if ($connections > 1) {
                        $this->logger->info($channel->length() . ' connections currently.');
                    }

                    go([new $clientClassName($clientStream, $this->channels), 'start']);
                }
            }
        }
    }

}
