<?php
namespace Magnolia\Server\API;

use Magnolia\Contract\ServerInterface;
use Magnolia\Exception\ServerInterruptException;
use Monolog\Logger;
use Magnolia\Server\GenericServer;

final class Api extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'API.Server';
    protected $instantiationClientClassName = \Magnolia\Client\API\Api::class;

    public function getServerName(): string
    {
        return 'API';
    }

    public function getListenHost(): string
    {
        return getenv('API_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('API_LISTEN_PORT');
    }
}
