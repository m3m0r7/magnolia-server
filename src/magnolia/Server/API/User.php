<?php
namespace Magnolia\Server\API;

use Magnolia\Contract\ServerInterface;
use Magnolia\Exception\ServerInterruptException;
use Monolog\Logger;
use Magnolia\Server\GenericServer;

final class User extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'APIUser.Server';
    protected $instantiationClientClassName = \Magnolia\Client\API\User::class;

    public function getServerName(): string
    {
        return 'APIUser';
    }

    public function getListenHost(): string
    {
        return getenv('API_USER_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('API_USER_LISTEN_PORT');
    }
}
