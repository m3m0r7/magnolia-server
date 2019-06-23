<?php
namespace Magnolia\Contract;

use Magnolia\Stream\Stream;

interface ClientInterface
{
    public function __construct(Stream $client);

    public function start(): void;
}

