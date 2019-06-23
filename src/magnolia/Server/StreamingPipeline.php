<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Monolog\Logger;

final class StreamingPipeline extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'StreamingPipeline';

    public function getServerName(): string
    {
        return 'StreamingPipeline';
    }

    public function getListenHost(): string
    {
        return getenv('STREAMING_PIPELINE_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('STREAMING_PIPELINE_LISTEN_PORT');
    }

    public function getInstantiationClientClassName(): ?string
    {
        return null;
    }
}

