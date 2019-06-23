<?php
namespace Magnolia\Contract;

interface TimerInterface
{
    public function __construct(array &$channels = []);

    public function run(): void;

    /**
     * Set interval time as millisecond.
     * @return int
     */
    public static function getIntervalTime(): int;
}

