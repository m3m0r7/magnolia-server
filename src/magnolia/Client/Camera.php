<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Stream\Stream;
use Monolog\Logger;
use Swoole\Coroutine\Channel;

final class Camera extends AbstractClient implements ClientInterface
{
    use \Magnolia\Traits\ClientManageable;

    protected $loggerChannelName = 'Camera.Client';
    protected $loggerLevel = Logger::DEBUG;

    public function start(): void
    {
        /**
         * @var Channel $channel
         */
        $channel = $this->channels[\Magnolia\Server\StreamingPipeline::class];

        $promiseCounter = new \Swoole\Atomic(0);
        while (true) {
            while ($sizePacket = $this->client->read(4)) {
                $size = current(unpack('L', $sizePacket));
                if ($size === 0) {
                    continue;
                }
                $packet = $this->client->read($size);

                // initialize to default value
                $promiseCounter->set(0);

                $targetClients = $channel->length();

                while (!$channel->isEmpty()) {
                    $client = $channel->pop();

                    /**
                     * @var Stream $client
                     */
                    go(function () use ($client, $packet, $promiseCounter) {
                        // send packets
                        $client
                            ->writeLine('--' . $client->getUUID())
                            ->writeLine('Content-Type: image/jpeg')
                            ->writeLine('Content-Length: ' . strlen($packet))
                            ->writeLine('')
                            ->write($packet);

                        // add counter
                        $promiseCounter->add(1);
                    });
                }

                // Waiting proceeded.
                while($promiseCounter->get() < $targetClients);
            }
        }
    }
}
