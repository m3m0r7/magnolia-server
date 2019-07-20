<?php
namespace Magnolia\Traits;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Operation\Middleware\Query;
use Magnolia\Stream\Stream;

/**
 * @property-read Stream $client
 */
trait HTTPInfoAssignable
{
    /**
     * @var string
     */
    protected $method = '';

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var string
     */
    protected $queryString = '';

    /**
     * @var Query
     */
    protected $query = null;

    public function assignHttpInfo(array $headers = []): void
    {
        $firstLine = preg_replace('/\s+/', ' ', $headers[0] ?? '');
        $header = explode(' ', $firstLine);
        if (count($header) < 2) {
            $this->disconnect();
            return;
        }

        [ $this->method, $this->path ] = $header;
        $splitPath = explode('?', $this->path, 2);
        $this->path = $splitPath[0] ?? null;

        $this->queryString = $splitPath[1] ?? '';
        $this->query = new Query($this->queryString);
    }
}