<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\ProcedureKeys;
use Magnolia\Enum\Runtime;
use Magnolia\Enum\SynchronizerKeys;
use Magnolia\Enum\Validation;
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
    use \Magnolia\Traits\AuthKeyValidatable;

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

        $illegalCounter = [
            Validation::MAX_COUNT_VALIDATION_FRAME_MAGIC_BYTE => 0,
        ];

        $authKeySize = strlen(getenv('AUTH_KEY'));

        while (true) {
            $nextUpdateImage = 0;
            while ($authKey = $this->client->read($authKeySize)) {

                // Validate the first packet.
                if (!$this->isValidAuthKey($authKey)) {
                    $this->disconnect();
                    return;
                }

                $sizePacket = $this->client->read(4);
                $size = current(unpack('L', $sizePacket));
                if ($size === 0 || $size > getenv('MAX_CAMERA_FRAME_SIZE')) {
                    continue;
                }

                $this->logger->debug('Received ' . $size);

                $packet = $this->client->read($size);

                // validate jpeg
                if (!in_array(
                    substr($packet, 0, 4),
                    [
                        // Listed value is JPEG and JPG.
                        "\xff\xd8\xdd\xe0",
                        "\xff\xd8\xff\xee",
                        "\xff\xd8\xff\xdb",
                    ],
                    true
                )) {
                    $illegalCounter[Validation::MAX_COUNT_VALIDATION_FRAME_MAGIC_BYTE]++;
                    if ($illegalCounter[Validation::MAX_COUNT_VALIDATION_FRAME_MAGIC_BYTE] >= getenv(Validation::MAX_COUNT_VALIDATION_FRAME_MAGIC_BYTE)) {
                        // Not allowed connection.
                        $this->disconnect();
                        return;
                    }
                    continue;
                }

                // Reset illegal counter.
                $illegalCounter[Validation::MAX_COUNT_VALIDATION_FRAME_MAGIC_BYTE] = 0;

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
                    $this->proceedProcedure(ProcedureKeys::CAPTURE_FAVORITE, $packet);
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
