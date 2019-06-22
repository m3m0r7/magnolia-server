<?php
namespace Magnolia\Client;

interface ClientInterface
{
    public function __construct($client);

    public function start(): void;
}

