<?php
namespace Magnolia\Client\API\Contents;

abstract class AbstractAPIContents
{
    private $method;
    private $path;
    private $headers;

    public function __construct(string $method, string $path, array $headers = [])
    {
        $this->method = $method;
        $this->path = $path;
        $this->headers = $headers;
    }

    public function getBody()
    {
        return [];
    }
}
