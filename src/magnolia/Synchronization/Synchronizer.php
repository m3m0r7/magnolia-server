<?php
namespace Magnolia\Synchronization;

use Magnolia\Contract\TimerInterface;
use Magnolia\Enum\SynchronizerKeys;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Swoole\Atomic;

final class Synchronizer
{
    const IS_UNLOCKED = 0;
    const IS_LOCKED = 1;

    /**
     * @var Atomic
     */
    protected $status;

    public function __construct()
    {
        $this->status = new Atomic(0);
    }

    public function isUnlocked(): bool
    {
        return $this->status === static::IS_UNLOCKED;
    }

    public function isLocked(): bool
    {
        return $this->status === static::IS_LOCKED;
    }

    public function lock()
    {
        $this->wait();
        $this->status->set(static::IS_LOCKED);
    }

    public function unlock()
    {
        $this->status->set(static::IS_UNLOCKED);
    }

    public function wait(int $timeout = 0): void
    {
        while($this->isLocked());
    }

    public function synchronize(callable $callback, int $timeout = 0): self
    {
        $this->lock();
        $callback();
        $this->unlock();
        return $this;
    }
}