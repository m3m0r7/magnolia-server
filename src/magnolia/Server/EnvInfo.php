<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Magnolia\Exception\ServerInterruptException;
use Monolog\Logger;

final class EnvInfo extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'EnvInfo.Server';
    protected $instantiationClientClassName = \Magnolia\Client\EnvInfo::class;

    public function getServerName(): string
    {
        return 'EnvInfo';
    }

    public function getListenHost(): string
    {
        return getenv('ENV_INFO_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('ENV_INFO_LISTEN_PORT');
    }
}
