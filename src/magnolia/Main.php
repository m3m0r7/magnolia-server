<?php
namespace Magnolia;

use Magnolia\Contract\TimerInterface;
use Magnolia\Enum\SynchronizerKeys;
use Magnolia\Synchronization\Synchronizer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class Main
{
    private $events = [];

    public function register(string $className): self
    {
        $this->events[] = $className;
        return $this;
    }

    public function run(): void
    {
        // create channels
        $channels = [];
        $procedures = [];
        foreach ($this->events as $eventClass) {
            /**
             * @var string $eventClass
             */
            $channels[$eventClass] = new \Swoole\Coroutine\Channel(
                (int) getenv('MAX_CONNECTIONS')
            );
            $procedures[$eventClass] = new \Swoole\Coroutine\Channel(
                (int) getenv('MAX_PROCEDURE_STACKS')
            );
            $procedures[$eventClass::getInstantiationClientClassName()] = new \Swoole\Coroutine\Channel(
                (int) getenv('MAX_PROCEDURE_STACKS')
            );
        }

        $synchronizers = [];
        $synchronizerKeyClassObject = new \ReflectionClass(SynchronizerKeys::class);
        foreach (array_keys($synchronizerKeyClassObject->getConstants()) as $key) {
            $synchronizers[$key] = new Synchronizer();
        }


        foreach ($this->events as $eventClass) {
            /**
             * @var \Magnolia\Contract\ServerInterface $event
             */
            $event = new $eventClass($channels, $synchronizers, $procedures);
            if ($event instanceof TimerInterface) {
                // if event type is a timer, then run with Swoole\Timer.
                $channels[$eventClass]->push(
                    \Swoole\Timer::tick(
                        $event::getIntervalTime(),
                        [$event, 'run']
                    )
                );
            } else {
                go([$event, 'run']);
            }
        }
        \Swoole\Event::wait();
    }
}
