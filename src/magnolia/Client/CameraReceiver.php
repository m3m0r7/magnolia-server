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

final class CameraReceiver extends AbstractClient implements ClientInterface
{
    use \Magnolia\Traits\ClientManageable;
    use \Magnolia\Traits\ProcedureManageable;
    use \Magnolia\Traits\AuthKeyValidatable;

    protected $loggerChannelName = 'Camera.Client';

    protected $io = null;

    public function __construct(Stream $client, array &$channels = [], array &$synchronizers = [], array &$procedures = [])
    {
        parent::__construct($client, $channels, $synchronizers, $procedures);
        $this->io = fopen(sys_get_temp_dir() . '/camera.jpg', 'r+');
    }

    public function start(): void
    {
        \Swoole\Runtime::enableCoroutine();

        /**
         * @var Channel $channel
         * @var Synchronizer $synchronizer
         */
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

                // Write buffer io
                if (flock($this->io, LOCK_EX)) {
                    rewind($this->io);
                    ftruncate($this->io, 0);
                    fwrite($this->io, $packet);
                    flock($this->io, LOCK_UN);
                }
            }
        }
    }
}
