<?php
namespace Magnolia\Contract;

interface ServerInterface
{
    public function __construct(array &$channels = []);

    public function run(): void;

    public function getServerName(): string;

    public function getListenHost(): string;

    public function getListenPort(): int;
}

