<?php
namespace Magnolia\Server;

interface ServerInterface
{
    public function run(): void;

    public function getServerName(): string;

    public function getListenHost(): string;

    public function getListenPort(): int;

    public function getClientClassName();
}

