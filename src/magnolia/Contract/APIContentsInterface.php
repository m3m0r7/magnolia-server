<?php
namespace Magnolia\Contract;

interface APIContentsInterface
{
    public function __construct(string $method, string $path, string $queryString, array $headers = [], ?string $body = null);

    public function getResponseBody(): array;

    public function getResponseHeaders(): array;

    public function setStatus(int $status): APIContentsInterface;

    public function getStatus(): int;

    public function setContentType(string $contentType);

    public function getContentType();

    public function __toString(): string;
}
