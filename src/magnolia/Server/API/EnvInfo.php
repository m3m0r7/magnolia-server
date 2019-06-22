<?php
namespace Magnolia\Server\API;

use Magnolia\Client\ClientInterface;
use Magnolia\Exception\ServerInterruptException;
use Monolog\Logger;
use Magnolia\Server\GenericServer;
use Magnolia\Server\ServerInterface;

final class EnvInfo extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'APIEnvInfo.Server';
    protected $loggerLevel = Logger::DEBUG;

    public function getServerName(): string
    {
        return 'APIEnvInfo';
    }

    public function getListenHost(): string
    {
        return getenv('API_ENV_INFO_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('API_ENV_INFO_LISTEN_PORT');
    }

    public function getClientClassName()
    {
        return \Magnolia\Client\API\EnvInfo::class;
    }
}
