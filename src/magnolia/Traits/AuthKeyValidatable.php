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
trait AuthKeyValidatable
{
    public function isValidAuthKey(?string $authKey): bool
    {
        if ($authKey === getenv('AUTH_KEY')) {
            return true;
        }
        return false;
    }
}