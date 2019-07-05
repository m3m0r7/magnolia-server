<?php
namespace Magnolia\Server\API;

use Magnolia\Contract\ServerInterface;
use Magnolia\Exception\ServerInterruptException;
use Monolog\Logger;
use Magnolia\Server\GenericServer;

final class Image extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'APIImage.Server';
    protected $instantiationClientClassName = \Magnolia\Client\API\Image::class;

    public function getServerName(): string
    {
        return 'APIImage';
    }

    public function getListenHost(): string
    {
        return getenv('API_IMAGE_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('API_IMAGE_LISTEN_PORT');
    }
}
