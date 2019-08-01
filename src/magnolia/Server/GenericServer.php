<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Magnolia\Exception\ServerInterruptException;
use Magnolia\Stream\Stream;
use Magnolia\Synchronization\Synchronizer;
use Monolog\Logger;

class GenericServer extends AbstractServer implements ServerInterface
{
    use \Magnolia\Traits\SecureConnectionManageable;

    /**
     * @throws ServerInterruptException
     */
    public function run(): void
    {
        \Swoole\Runtime::enableCoroutine();

        while (true) {
            $context = stream_context_create();

            if ($this->isEnabledTLS()) {
                // Write SSL Context
                $this->writeTLSContext($context);
            }

            $server = stream_socket_server(
                sprintf(
                    ($this->isEnabledTLS() ? 'tls' : 'tcp') . '://%s:%d',
                    $this->getListenHost(),
                    $this->getListenPort(),
                ),
                $errno,
                $errstr,
                STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
                $context
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
             * @var Synchronizer|null $synchronizer
             */
            $channel = $this->channels[static::class];
            $synchronizer = $this->synchronizers[$this->synchronizeKey] ?? null;
            $this->logger->info('Listening started.');
            while (true) {
                try {
                    while ($client = @stream_socket_accept($server, 0)) {

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
                        go(function () use ($synchronizer, $client, $channel) {
                            // Lock with synchronizer, the stream does not allowed to write at the same time.
                            if ($synchronizer !== null) {
                                $synchronizer->lock();
                            }

                            $streamClass = $this->clientStreamClass;
                            $clientStream = new $streamClass($client);
                            $channel->push($clientStream);

                            // unlock
                            if ($synchronizer !== null) {
                                $synchronizer->unlock();
                            }

                            $connections = $channel->length();
                            $this->logger->info($channel->length() . ' connections currently.');

                            // If $instantiationClientClassName is null, it means the server not having reacting event.
                            if (static::$instantiationClientClassName !== null) {
                                $instantiationClientClassName = static::$instantiationClientClassName;
                                go([
                                    new $instantiationClientClassName(
                                        $clientStream,
                                        $this->channels,
                                        $this->synchronizers,
                                        $this->procedures
                                    ),
                                    'start'
                                ]);
                            }
                        });
                    }
                } catch (\ErrorException $e) {
                    // Nothing to do.
                }
            }
        }
    }

}
