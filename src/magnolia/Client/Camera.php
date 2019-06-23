<?php
namespace Magnolia\Client;

use Monolog\Logger;

final class Camera extends AbstractClient implements ClientInterface
{
    protected $loggerChannelName = 'Camera.Client';
    protected $loggerLevel = Logger::DEBUG;

    public function start(): void
    {
        while (true) {
            while ($sizePacket = $this->client->read(4)) {
                $size = current(unpack('L', $sizePacket));
                var_dump($size);
                if ($size === 0) {
                    continue;
                }
                $packet = $this->client->read($size);
            }
        }
    }
}
