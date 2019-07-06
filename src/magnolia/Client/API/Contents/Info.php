<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;

final class Info extends AbstractAPIContents
{
    use \Magnolia\Traits\Redis;

    public function getBody()
    {

        $parameters = $this->getRedis()->hGetAll(RedisKeys::ENV_INFO);

        $data = [
            'temperature'       => (float) $parameters[KindEnv::KIND_TEMPERATURE],
            'humidity'          => (float) $parameters[KindEnv::KIND_HUMIDITY],
            'pressure'          => (float) $parameters[KindEnv::KIND_PRESSURE],
            'cpu_temperature'   => (float) $parameters[KindEnv::KIND_CPU_TEMPERATURE],
        ];

        // Adjustment values
        $data['temperature'] = round(
            $data['temperature'] - 22,
            2
        );
        $data['humidity'] = round(
            $data['humidity'] * 2.52,
            2
        );

        foreach ($data as &$value) {
            if ($value <= 0) {
                $value = null;
            }
        }
        return $data;
    }
}
