<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;

final class Info extends AbstractAPIContents implements APIContentsInterface
{
    use \Magnolia\Traits\Redis;

    public function getResponseBody(): array
    {
        if (!$this->getSession()->has('user')) {
            return $this->returnUnauthorized(
                'You did not logged-in.'
            );
        }

        $parameters = [];
        $hashes = $this->getRedis()->hGetAll(RedisKeys::ENV_INFO);
        for ($i = 0; $i < count($hashes); $i += 2) {
            $parameters[$hashes[$i]] = $hashes[$i + 1];
        }

        $data = [
            'temperature'       => (float) ($parameters[KindEnv::KIND_TEMPERATURE] ?? 0),
            'humidity'          => (float) ($parameters[KindEnv::KIND_HUMIDITY] ?? 0),
            'pressure'          => (float) ($parameters[KindEnv::KIND_PRESSURE] ?? 0),
            'cpu_temperature'   => (float) ($parameters[KindEnv::KIND_CPU_TEMPERATURE] ?? 0),
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

        parent::getResponseBody();

        return [
            'info' => $data,
            'versions' => [
                'device' => [
                    'number' => '0.0.0',
                    'code'   => 'Magnolia',
                    'extra'  => 'Raspbian',
                ],
                'app' => [
                    'number' => '0.0.0',
                    'code'   => 'Magnolia',
                ],
                'live_streaming' => [
                    'number' => '0.0.0',
                    'code'   => 'Magnolia',
                ],
            ],
        ];
    }
}
