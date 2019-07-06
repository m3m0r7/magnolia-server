<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Traits\APIResponseable;
use Magnolia\Traits\CookieUsable;
use Magnolia\Traits\SessionUsable;

abstract class AbstractAPIContents implements APIContentsInterface
{
    use APIResponseable;
    use CookieUsable;
    use SessionUsable;

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

        // Enable cookie
        $this->enableCookie();

        // Enable session
        $this->getSession()->enable();
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

    public function getResponseBody(): array
    {
        return [];
    }

    public function getResponseHeaders(): array
    {
        return $this->buildCookies();
    }

    public function __toString(): string
    {
        return json_encode($this->getResponseBody());
    }
}
