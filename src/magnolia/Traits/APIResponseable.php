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
trait APIResponseable
{
    public function returnForbidden(string $message = null): array
    {
        $this->setStatus(403);
        return [
            'status' => 403,
            'error' => $message,
        ];
    }

    public function returnBadRequest(string $message = null): array
    {
        $this->setStatus(400);
        return [
            'status' => 400,
            'error' => $message,
        ];
    }

    public function returnUnauthorized(string $message = null): array
    {
        $this->setStatus(401);
        return [
            'status' => 401,
            'error' => $message,
        ];
    }
}
