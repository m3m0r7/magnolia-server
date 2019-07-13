<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Magnolia\Enum\SynchronizerKeys;
use Magnolia\Stream\WebSocketStream;
use Monolog\Logger;

final class StreamingPipeline extends GenericServer implements ServerInterface
{
    protected $loggerChannelName = 'StreamingPipeline';
    protected $instantiationClientClassName = \Magnolia\Client\StreamingPipeline::class;
    protected $synchronizeKey = SynchronizerKeys::CLIENT_FROM_STREAMING_PIPELINE;
    protected $clientStreamClass = WebSocketStream::class;

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
}

