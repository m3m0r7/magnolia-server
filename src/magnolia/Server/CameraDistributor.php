<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Magnolia\Stream\WebSocketStream;
use Monolog\Logger;

final class CameraDistributor extends AbstractServer implements ServerInterface
{
    protected $loggerChannelName = 'CameraDistributor';
    use \Magnolia\Traits\SecureConnectionManageable;

    public function run(): void
    {

        $fd = inotify_init();
        $watch_descriptor = inotify_add_watch(
            $fd,
            sys_get_temp_dir() . '/camera.jpg',
            IN_MODIFY
        );

        $channel = new \Swoole\Coroutine\Channel();
        $context = stream_context_create();

        \Swoole\Event::add(
            $fd,
            function ($fd) use ($channel) {
                $temporary = [];
                while ($client = $channel->pop()) {
                    /**
                     * @var WebSocketStream $client
                     */
                    
                }

                foreach ($temporary as $client) {
                    $channel->push($client);
                }
            }
        );

        $server = stream_socket_server(
            sprintf(
                ($this->isEnabledTLS() ? 'tls' : 'tcp') . '://%s:%d',
                $this->getListenHost(),
                $this->getListenPort(),
            ),
            $errno,
            $errstr,
            STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,
            $context
        );

        while (true) {
            while ($client = stream_socket_accept($server, 0)) {
                $channel->push(
                    new WebSocketStream($client)
                );
            }
        }
        \Swoole\Event::wait();
    }

    public function getServerName(): string
    {
        return 'CameraDistributor';
    }

    public function getListenHost(): string
    {
        return getenv('CAMERA_DISTRIBUTOR_LISTEN_HOST');
    }

    public function getListenPort(): int
    {
        return getenv('CAMERA_DISTRIBUTOR_LISTEN_PORT');
    }
}
