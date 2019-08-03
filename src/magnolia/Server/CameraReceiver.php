<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Monolog\Logger;

final class CameraReceiver extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'CameraReceiver';
    protected $loggerLevel = Logger::DEBUG;
    protected $enableSSL = false;

    protected static $instantiationClientClassName = \Magnolia\Client\CameraReceiver::class;

    public function getServerName(): string
    {
        return 'CameraReceiver';
    }

    public function getListenHost(): string
    {
        return getenv('CAMERA_RECEIVER_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('CAMERA_RECEIVER_LISTEN_PORT');
    }
}

