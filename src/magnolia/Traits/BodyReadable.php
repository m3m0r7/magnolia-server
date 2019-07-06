<?php
namespace Magnolia\Traits;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Enum\SynchronizerKeys;
use Magnolia\Stream\Stream;
use Magnolia\Synchronization\Synchronizer;
use Swoole\Coroutine\Channel;

/**
 * @property-read Stream $client
 */
trait BodyReadable
{
    protected $requestBody = null;

    public function proceedBody(array $headers): bool
    {
        $contentLength = (int) ($headers['content-length'] ?? 0);
        if ($contentLength <= 0) {
            return false;
        }
        $this->requestBody = $this->client->read(
            $contentLength
        );
        return true;
    }
}