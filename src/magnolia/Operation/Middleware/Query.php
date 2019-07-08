<?php
namespace Magnolia\Operation\Middleware;

final class Query
{
    private $queryString;
    private $queries = [];

    public function __construct(string $queryString)
    {
        $this->queryString = $queryString;
        parse_str($queryString, $this->queries);
    }

    public function get(string $key)
    {
        return $this->queries[$key] ?? null;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->queries);
    }
}
