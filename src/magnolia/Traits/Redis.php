<?php
namespace Magnolia\Traits;

trait Redis
{
    public function getRedis(): \Redis
    {
        static $redis = null;
        if ($redis === null) {
            $redis = new \Redis();
            $redis->connect(
                getenv('REDIS_HOST'),
                getenv('REDIS_PORT'),
            );
        }

        return $redis;
    }
}
