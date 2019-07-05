<?php
namespace Magnolia\Client\API;

use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;
use Magnolia\Utility\Functions;
use Monolog\Logger;
use Magnolia\Client\AbstractClient;

final class Image extends AbstractClient implements ClientInterface
{
    protected $loggerChannelName = 'APIImage.Client';
    public function start(): void
    {

    }
}
