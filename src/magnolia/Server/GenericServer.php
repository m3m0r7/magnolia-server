<?php
namespace Magnolia\Server;

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
        \Swoole\Runtime::enableCoroutine();
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
                    // Check channel connections.
                    if ($channel->isFull()) {
                        $this->logger->info(
                            'Failed to connect because it is over MAX_CONNECTIONS.'
                        );

                        // Force closing session.
                        fclose($client);
                        continue;
                    }

                    // In one case, Google Chrome send 2 connections (pre-flight and fetching document data).
                    // So, It need asynchronously processing.
                    $clientStream = new Stream($client);
                    $channel->push($clientStream);

                    $connections = $channel->length();
                    $this->logger->info($channel->length() . ' connections currently.');

                    // If $instantiationClientClassName is null, it means the server not having reacting event.
                    if ($this->instantiationClientClassName !== null) {
                        $instantiationClientClassName = $this->instantiationClientClassName;
                        go([
                            new $instantiationClientClassName(
                                $clientStream,
                                $this->channels,
                                $this->synchronizers
                            ),
                            'start'
                        ]);
                    }
                }
            }
        }
    }

}
