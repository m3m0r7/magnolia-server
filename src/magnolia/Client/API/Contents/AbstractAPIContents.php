<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Contract\ClientInterface;
use Magnolia\Operation\Middleware\Query;
use Magnolia\Traits\APIResponseable;
use Magnolia\Traits\CookieUsable;
use Magnolia\Traits\SessionUsable;

abstract class AbstractAPIContents implements APIContentsInterface
{
    use APIResponseable;
    use CookieUsable;
    use SessionUsable;

    /**
     * @var ClientInterface $client
     */
    protected $client;
    protected $method;
    protected $path;
    protected $query;
    protected $headers;
    protected $content;
    protected $status = 200;
    protected $contentType = 'application/json';

    public function __construct(
        ClientInterface $client,
        string $method,
        string $path,
        Query $query,
        array $headers = [],
        ?string $content = null
    ) {
        $this->client = $client;
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->query = $query;
        $this->headers = $headers;
        $this->content = $content;

        // Enable cookie
        $this->enableCookie();

        // Enable session
        $this->getSession()->enable();
    }

    public function getQuery(): Query
    {
        return $this->query;
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

    public function setContentType(string $contentType): APIContentsInterface
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function getContentType()
    {
        switch ($this->contentType) {
            case 'jpg':
                return 'image/jpeg';
            case 'png':
                return 'image/png';
        }
        return $this->contentType;
    }

    public function getResponseBody(): array
    {
        $this->getSession()->emit();
        return $this->returnOK();
    }

    public function getResponseHeaders(): array
    {
        return [
            'Access-Control-Allow-Origin' => $this->headers['origin'] ?? '*',
            'Access-Control-Allow-Credentials' => 'true',
        ] + $this->buildCookies();
    }

    public function __toString(): string
    {
        $body = $this->getResponseBody();
        [ $prefix ] = explode('/', $this->getContentType());

        // For Image
        if ($prefix === 'image') {
            return stream_get_contents($body['body']) ?? '';
        }
        return json_encode($body);
    }
}
