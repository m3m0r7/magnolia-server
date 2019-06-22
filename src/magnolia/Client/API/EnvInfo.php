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
    protected $loggerLevel = Logger::DEBUG;

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

        // Write headers section.
        $this->client
            ->writeLine("HTTP/1.1 200 OK")
            ->writeLine("Content-Length: 4")
            ->writeLine("");

        // Write body sections.
        $this->client
            ->write("TEST");
    }
}
