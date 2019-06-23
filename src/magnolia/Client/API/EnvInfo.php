<?php
namespace Magnolia\Client\API;

use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;
use Magnolia\Utility\Functions;
use Monolog\Logger;
use Magnolia\Client\AbstractClient;
use Magnolia\Client\ClientInterface;

final class EnvInfo extends AbstractClient implements ClientInterface
{
    use \Magnolia\Traits\Redis;

    protected $loggerChannelName = 'APIEnvInfo.Client';

    public function start(): void
    {
        $responseHeaders = [];
        $readLength = 0;
        while ($line = $this->client->readLine()) {
            if (ltrim($line, "\r") === "\n") {
                break;
            }
            if ($line === '') {
                // No data.
                return;
            }

            $readLength += strlen($line);
            if (((int) getenv('MAX_HEADER_LENGTH')) < $readLength) {
                return;
            }

            $responseHeaders[] = rtrim($line, "\n");
        }

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

        $data = json_encode($data);

        // Enable Buffer
        $this->client->enableBuffer(true);

        // Write headers section.
        $this->client
            ->writeLine("HTTP/1.1 200 OK")
            ->writeLine("Content-Type: application/json")
            ->writeLine("Content-Length: " . strlen($data))
            ->writeLine("");

        // Write body sections.
        $this->client
            ->write($data);

        // Emit
        $this->client->emit();

        // Close connection
        $this->client->close();
    }
}
