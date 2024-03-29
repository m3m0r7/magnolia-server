<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;
use Magnolia\Utility\Functions;
use Monolog\Logger;

final class EnvInfo extends AbstractClient implements ClientInterface
{
    use \Magnolia\Traits\Redis;

    protected $loggerChannelName = 'EnvInfo.Client';

    public function start(): void
    {
        $readStartingTag = current(unpack('C', $this->client->read(1)));
        if ($readStartingTag !== KindEnv::KIND_READ_STARTING) {
            return;
        }

        $receivingCount = current(unpack('C', $this->client->read(1)));

        $envs = [
            KindEnv::KIND_TEMPERATURE => null,
            KindEnv::KIND_HUMIDITY => null,
            KindEnv::KIND_PRESSURE => null,
            KindEnv::KIND_CPU_TEMPERATURE => null,
        ];
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
                case KindEnv::KIND_TEMPERATURE:
                case KindEnv::KIND_HUMIDITY:
                case KindEnv::KIND_PRESSURE:
                case KindEnv::KIND_CPU_TEMPERATURE:
                    $highByte = current(unpack('V', $this->client->read(4)));
                    $lowByte = current(unpack('V', $this->client->read(4)));
                    $value = (float) ($highByte . '.' . $lowByte);
                    break;
                default:
                    // Unknown kind tag
                    return;
            }
            $envs[$kindTag] = $value;
            $this->logger->debug(
                'Received packet',
                [
                    $value,
                ]
            );
        }

        // Do caching env information to Redis.
        $this->getRedis()->del(RedisKeys::ENV_INFO);
        foreach ($envs as $kindTag => $value) {
            $this->getRedis()->hSet(
                RedisKeys::ENV_INFO,
                $kindTag,
                $value
            );
        }
    }
}
