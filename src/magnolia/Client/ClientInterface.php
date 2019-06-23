<?php
namespace Magnolia\Client;

use Magnolia\Stream\Stream;

interface ClientInterface
{
    public function __construct(Stream $client);

    public function start(): void;
}

