<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\ProcedureKeys;
use Magnolia\Enum\Runtime;
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
    use \Magnolia\Traits\ClientManageable;
    use \Magnolia\Traits\ProcedureManageable;

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

                if ($nextUpdateImage < time()) {
                    $nextUpdateImage = time() + Runtime::UPDATE_IMAGE_INTERVAL;
                    Storage::put(
                        '/record/image.jpg',
                        $packet,
                        [
                            'updated_at' => $nextUpdateImage - Runtime::UPDATE_IMAGE_INTERVAL,
                            'next_update' => $nextUpdateImage,
                        ]
                    );
                }

                // if channel is empty, don't proceed to send image packet to client.
                // otherwise, send image packet to client in a coroutine.
                if ($channel->isEmpty()) {
                    continue;
                }

                go(function () use ($packet, $channel, $synchronizer) {
                    $this->proceedProcedure(
                        ProcedureKeys::CAPTURE_FAVORITE,
                        $packet
                    );
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
