<?php
namespace Magnolia;

use Magnolia\Server\ServerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class Main
{
    private $serverList = [];

    public function __construct(array $serverList = [])
    {
        $this->serverList = $serverList;
    }

    public function run(): void
    {
        \Swoole\Runtime::enableCoroutine();
        foreach ($this->serverList as $server) {
            /**
             * @var ServerInterface $server
             */
            go([new $server(), 'run']);
        }
        \Swoole\Event::wait();
    }
}
