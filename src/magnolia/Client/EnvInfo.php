<?php
namespace Magnolia\Client;

use Magnolia\Utility\Functions;
use Monolog\Logger;

final class EnvInfo extends AbstractClient implements ClientInterface
{
    protected $loggerChannelName = 'EnvInfo.Client';
    protected $loggerLevel = Logger::DEBUG;

    public function start(): void
    {
        $readStartingTag = current(unpack('C', $this->client->read(1)));
        if ($readStartingTag !== 0xFF) {
            return;
        }

        $receivingCount = current(unpack('C', $this->client->read(1)));

        for ($i = 0; $i < $receivingCount; $receivingCount--) {
            // read KindTag
            $kindTag = current(unpack('C', $this->client->read(1)));
            $this->logger->debug(
                'Received Kind Tag',
                [
                    sprintf('0x%02X', $kindTag),
                ]
            );
            $value = null;
            switch ($kindTag) {
                case 0x00: // Temperature
                case 0x10: // Humidity
                case 0x20: // Pressure
                case 0x30: // CPU Temperature
                    $highByte = current(unpack('V', $this->client->read(4)));
                    $lowByte = current(unpack('V', $this->client->read(4)));

                    $value = (float) ($highByte . '.' . $lowByte);
                    break;
            }
            $this->logger->debug(
                'Received packet',
                [
                    $value,
                ]
            );
        }
    }
}
