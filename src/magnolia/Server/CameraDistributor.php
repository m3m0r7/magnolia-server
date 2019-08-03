<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Monolog\Logger;

final class CameraDistributor extends AbstractServer implements ServerInterface
{
    protected $loggerChannelName = 'CameraDistributor';

    public function run(): void
    {
        $fd = inotify_init();
        $watch_descriptor = inotify_add_watch(
            $fd,
            sys_get_temp_dir() . '/camera.jpg',
            IN_MODIFY
        );

        \Swoole\Event::add(
            $fd,
            function ($fd) {
                var_dump('Received!', $fd);
            }
        );
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
