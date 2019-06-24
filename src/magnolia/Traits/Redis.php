<?php
namespace Magnolia\Traits;

trait Redis
{
    private $redisConnection = null;

    public function getRedis(): \Swoole\Coroutine\Redis
    {
        if ($this->redisConnection === null) {
            $this->redisConnection = new \Swoole\Coroutine\Redis();
            $this->redisConnection->connect(
                getenv('REDIS_HOST'),
                getenv('REDIS_PORT'),
            );
        }

        return $this->redisConnection;
    }
}
