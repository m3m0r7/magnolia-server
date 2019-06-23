<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Monolog\Logger;

final class Camera extends AbstractClient implements ClientInterface
{
    use \Magnolia\Traits\ClientManageable;

    protected $loggerChannelName = 'Camera.Client';
    protected $loggerLevel = Logger::DEBUG;

    public function start(): void
    {
        while (true) {
            while ($sizePacket = $this->client->read(4)) {
                $size = current(unpack('L', $sizePacket));
                if ($size === 0) {
                    continue;
                }
                $packet = $this->client->read($size);
            }
        }
    }
}
