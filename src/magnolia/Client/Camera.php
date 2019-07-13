<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\SynchronizerKeys;
use Magnolia\Stream\Stream;
use Magnolia\Stream\WebSocketStream;
use Magnolia\Synchronization\Synchronizer;
use Magnolia\Utility\Storage;
use Magnolia\Utility\WebSocket;
use Monolog\Logger;
use Swoole\Coroutine\Channel;

final class Camera extends AbstractClient implements ClientInterface
{
    const UPDATE_IMAGE_INTERVAL = 30;
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
            $nextUpdateImage = 0;
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

                if ($nextUpdateImage < time()) {
                    $nextUpdateImage = time() + static::UPDATE_IMAGE_INTERVAL;
                    Storage::put(
                        '/record/image.jpg',
                        $packet,
                        [
                            'updated_at' => $nextUpdateImage - static::UPDATE_IMAGE_INTERVAL,
                            'next_update' => $nextUpdateImage,
                        ]
                    );
                }

                go(function () use ($packet, $channel, $synchronizer) {

                    $synchronizer->lock();
                    $tempClientConnections = [];
                    while (!$channel->isEmpty()) {
                        /**
                         * @var WebSocketStream $client
                         */
                        $client = $channel->pop();
                        if ($client->isDisconnected()) {
                            continue;
                        }

                        if (!$client->isEstablishedHandshake()) {
                            continue;
                        }

                        // send packets
                        $client
                            ->enableBuffer(false)
                            ->write(
                                WebSocket::encodeMessage(
                                    $client,
                                    'data:image/jpeg;base64,' . base64_encode($packet)
                                )
                            );

                        $tempClientConnections[] = $client;
                    }

                    while (!empty($tempClientConnections)) {
                        $channel->push(
                            array_pop($tempClientConnections)
                        );
                    }
                    $synchronizer->unlock();
                });
            }
        }
    }
}
