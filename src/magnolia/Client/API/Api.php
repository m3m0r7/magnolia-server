<?php
namespace Magnolia\Client\API;

use Magnolia\Client\API\Contents\AbstractAPIContents;
use Magnolia\Client\API\Contents\PreflightRequest;
use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;
use Magnolia\Utility\Functions;
use Monolog\Logger;
use Magnolia\Client\AbstractClient;

final class Api extends AbstractClient implements ClientInterface
{
    private const ALLOWED_METHODS = [
        'GET', 'POST', 'PATCH', 'PUT', 'DELETE', 'OPTIONS',
    ];

    use \Magnolia\Traits\Redis;
    use \Magnolia\Traits\ClientManageable;
    use \Magnolia\Traits\HeaderReadable;
    use \Magnolia\Traits\BodyReadable;
    use \Magnolia\Traits\HTTPEmittable;

    protected $loggerChannelName = 'API.Client';

    public function start(): void
    {
        if (!$this->proceedHeaders()) {
            return;
        }

        $this->proceedBody($this->requestHeaders);

        $firstLine = preg_replace('/\s+/', ' ', $this->requestHeaders[0] ?? '');
        $header = explode(' ', $firstLine);
        if (count($header) < 2) {
            $this->disconnect();
            return;
        }

        [$method, $path] = $header;

        if (!in_array($method, static::ALLOWED_METHODS, true)) {
            $this->disconnect();
            return;
        }

        // API needs to allow pre-flight request.
        if ($method === 'OPTIONS') {
            $this->emit(
                new PreflightRequest(
                    $method,
                    $path,
                    $this->requestHeaders,
                    $this->requestBody
                )
            );
            return;
        }

        $routingInfo = $this->routingMap($path);
        if ($routingInfo === null) {
            $this->disconnect();
            return;
        }

        if (!in_array($method, $routingInfo['method'], true) &&
            !in_array('*', $routingInfo['method'], true)
        ) {
            $this->disconnect();
            return;
        }

        /**
         * @var AbstractAPIContents $class
         */
        $classPath = $routingInfo['resource'];
        $class = new $classPath($method, $path, $this->requestHeaders, $this->requestBody);
        $this->emit($class);
    }

    private function routingMap($path)
    {
        return [
            '/api/v1/login' => [
                'method' => ['POST'],
                'resource' => \Magnolia\Client\API\Contents\Login::class,
            ],
            '/api/v1/user' => [
                'method' => ['GET'],
                'resource' => \Magnolia\Client\API\Contents\User::class,
            ],
            '/api/v1/info' => [
                'method' => ['GET'],
                'resource' => \Magnolia\Client\API\Contents\Info::class,
            ],
            '/api/v1/favorite' => [
                'method' => ['GET'],
                'resource' => \Magnolia\Client\API\Contents\favorite::class,
            ],
        ][$path] ?? null;
    }
}
