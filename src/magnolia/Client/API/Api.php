<?php
namespace Magnolia\Client\API;

use Magnolia\Client\API\Contents\AbstractAPIContents;
use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;
use Magnolia\Utility\Functions;
use Monolog\Logger;
use Magnolia\Client\AbstractClient;

final class Api extends AbstractClient implements ClientInterface
{
    use \Magnolia\Traits\Redis;
    use \Magnolia\Traits\ClientManageable;
    use \Magnolia\Traits\HeaderReadable;

    protected $loggerChannelName = 'API.Client';
    public function start(): void
    {
        if (!$this->proceedHeaders()) {
            return;
        }

        $firstLine = preg_replace('/\s+/', ' ', $this->responseHeaders[0] ?? '');
        $header = explode(' ', $firstLine);
        if (count($header) < 2) {
            $this->disconnect();
            return;
        }

        [$method, $path] = $header;

        $classPath = $this->routingMap($path);
        if ($classPath === null) {
            $this->disconnect();
            return;
        }

        /**
         * @var AbstractAPIContents $class
         */
        $class = new $classPath($method, $path, $this->responseHeaders);
        $response = $class->getBody();
        $this->emit(json_encode($response));
    }

    private function routingMap($path)
    {
        return [
            '/api/v1/login' => \Magnolia\Client\API\Contents\User::class,
            '/api/v1/user' => \Magnolia\Client\API\Contents\User::class,
            '/api/v1/info' => \Magnolia\Client\API\Contents\Info::class,
            '/api/v1/favorite' => \Magnolia\Client\API\Contents\favorite::class,
        ][$path] ?? null;
    }
}
