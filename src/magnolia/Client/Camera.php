<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\SynchronizerKeys;
use Magnolia\Stream\Stream;
use Magnolia\Synchronization\Synchronizer;
use Monolog\Logger;
use Swoole\Coroutine\Channel;

final class Camera extends AbstractClient implements ClientInterface
{
    use \Magnolia\Traits\ClientManageable;

    protected $loggerChannelName = 'Camera.Client';
    protected $loggerLevel = Logger::DEBUG;

    public function start(): void
    {
        \Swoole\Runtime::enableCoroutine();
        /**
         * @var Channel $channel
         * @var Synchronizer $synchronizer
         */
        $channel = $this->channels[\Magnolia\Server\StreamingPipeline::class];
        $synchronizer = $this->synchronizers[SynchronizerKeys::CLIENT_FROM_STREAMING_PIPELINE];


        while (true) {
            while ($sizePacket = $this->client->read(4)) {
                $size = current(unpack('L', $sizePacket));
                if ($size === 0) {
                    continue;
                }

                $this->logger->debug('Received ' . $size);

                $packet = $this->client->read($size);

                // if channel is empty, don't proceed to send image packet to client.
                // otherwise, send image packet to client in a coroutine.
                if ($channel->isEmpty()) {
                    continue;
                }
                go(function () use ($packet, $channel, $synchronizer) {
                    $tempClientConnections = [];

                    while (!$channel->isEmpty()) {
                        /**
                         * @var Stream $client
                         */
                        $client = $channel->pop();
                        if ($client->isDisconnected()) {
                            continue;
                        }

                        // send packets
                        $synchronizer->lock();
                        $client
                            ->writeLine('--' . $client->getUUID())
                            ->writeLine('Content-Type: image/jpeg')
                            ->writeLine('Content-Length: ' . strlen($packet))
                            ->writeLine('')
                            ->writeLine($packet);
                        $synchronizer->unlock();

                        $tempClientConnections[] = $client;
                    }

                    while (!empty($tempClientConnections)) {
                        $channel->push(
                            array_pop($tempClientConnections)
                        );
                    }
                });
            }
        }
    }
}
