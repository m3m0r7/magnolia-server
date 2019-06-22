<?php
namespace Magnolia;

use Magnolia\Server\ServerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class Main
{
    private $serverList = [];

    public function register(string $className): self
    {
        $this->serverList[] = $className;
        return $this;
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
