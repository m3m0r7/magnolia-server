<?php
namespace Magnolia\Contract;

use Magnolia\Operation\Middleware\Query;

interface APIContentsInterface
{
    public function __construct(
        ClientInterface $client,
        string $method,
        string $path,
        Query $query,
        array $headers = [],
        ?string $content = null
    );

    public function getResponseBody(): array;

    public function getResponseHeaders(): array;

    public function setStatus(int $status): APIContentsInterface;

    public function getStatus(): int;

    public function setContentType(string $contentType);

    public function getContentType();

    public function __toString(): string;
}
