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

        // create channels
        $channels = [];
        foreach ($this->serverList as $serverClass) {
            $channels[$serverClass] = new \Swoole\Coroutine\Channel(
                (int) getenv('MAX_CONNECTIONS')
            );
        }

        foreach ($this->serverList as $serverClass) {
            /**
             * @var ServerInterface $serverClass
             */
            go([new $serverClass($channels), 'run']);
        }
        \Swoole\Event::wait();
    }
}
