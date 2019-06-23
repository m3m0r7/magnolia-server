<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Monolog\Logger;

final class Camera extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'Camera';
    protected $loggerLevel = Logger::DEBUG;

    public function getServerName(): string
    {
        return 'Camera';
    }

    public function getListenHost(): string
    {
        return getenv('CAMERA_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('CAMERA_LISTEN_PORT');
    }

    public function getClientClassName()
    {
        return \Magnolia\Client\Camera::class;
    }
}

