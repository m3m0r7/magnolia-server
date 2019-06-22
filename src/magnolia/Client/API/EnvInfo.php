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
    }
}
