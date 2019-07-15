<?php
namespace Magnolia\Client\API;

use Magnolia\Client\API\Contents\AbstractAPIContents;
use Magnolia\Client\API\Contents\NotFound;
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

        [ $method, $path ] = $header;
        $splitPath = explode('?', $path, 2);
        $path = $splitPath[0] ?? null;
        $queryString = $splitPath[1] ?? '';

        if (!in_array($method, static::ALLOWED_METHODS, true)) {
            $this->disconnect();
            return;
        }

        // API needs to allow pre-flight request.
        if ($method === 'OPTIONS') {
            $this->emit(
                new PreflightRequest(
                    $this,
                    $method,
                    $path,
                    $queryString,
                    $this->requestHeaders,
                    $this->requestBody
                )
            );
            return;
        }

        $routingInfo = $this->routingMap($path);

        if ($routingInfo === null ||
            (
                !in_array($method, $routingInfo['method'], true) &&
                !in_array('*', $routingInfo['method'], true)
            )
        ) {
            $this->emit(
                new NotFound(
                    $this,
                    $method,
                    $path,
                    $queryString,
                    $this->requestHeaders,
                    $this->requestBody
                )
            );
            return;
        }

        /**
         * @var AbstractAPIContents $class
         */
        $classPath = $routingInfo['resource'];
        $class = new $classPath(
            $this,
            $method,
            $path,
            $queryString,
            $this->requestHeaders,
            $this->requestBody
        );
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
                'method' => ['GET', 'POST'],
                'resource' => \Magnolia\Client\API\Contents\Favorite::class,
            ],
            '/api/v1/image' => [
                'method' => ['GET'],
                'resource' => \Magnolia\Client\API\Contents\Image::class,
            ],
            '/api/v1/capture' => [
                'method' => ['GET'],
                'resource' => \Magnolia\Client\API\Contents\Capture::class,
            ],
        ][$path] ?? null;
    }
}
