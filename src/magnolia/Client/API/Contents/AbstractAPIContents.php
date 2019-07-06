<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;

abstract class AbstractAPIContents implements APIContentsInterface
{
    protected $method;
    protected $path;
    protected $headers;
    protected $content;
    protected $status = 200;

    public function __construct(string $method, string $path, array $headers = [], ?string $content = null)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->headers = $headers;
        $this->content = $content;
    }

    public function setStatus(int $status): APIContentsInterface
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getBody(): array
    {
        return [];
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function __toString(): string
    {
        return json_encode($this->getBody());
    }
}
