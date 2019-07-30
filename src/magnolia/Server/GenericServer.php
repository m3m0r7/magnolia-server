<?php
namespace Magnolia\Server;

use Magnolia\Contract\ServerInterface;
use Magnolia\Exception\ServerInterruptException;
use Magnolia\Stream\Stream;
use Magnolia\Synchronization\Synchronizer;
use Monolog\Logger;

class GenericServer extends AbstractServer implements ServerInterface
{
    use \Magnolia\Traits\SecureConnectionManageable;

    public function listen(\Swoole\Server $server): void
    {
        $instantiateServer = $server->addListener(
            $this->getListenHost(),
            $this->getListenPort(),
            SWOOLE_SOCK_TCP | SWOOLE_SSL
        );

        $instantiateServer->set([
            'ssl_cert_file' => getenv('SSL_CERTIFICATE_FILE'),
            'ssl_key_file' => getenv('SSL_CERTIFICATE_KEY'),
            'ssl_verify_peer' => false,
            'ssl_allow_self_signed' => true,
        ]);

        $this->logger->info(
            'Server is running.',
            [
                "{$this->getListenHost()}:{$this->getListenPort()}"
            ]
        );

        $instantiateServer->on(
            'receive',
            function (\Swoole\Server $server, int $fd, int $reactorId, string $data) {
                /**
                 * @var \Swoole\Coroutine\Channel $channel
                 * @var Stream $clientStream
                 */
                $channel = $this->channels[static::class];
                $synchronizer = $this->synchronizers[$this->synchronizeKey] ?? null;

                // Check channel connections.
                if ($channel->isFull()) {
                    $this->logger->info(
                        'Failed to connect because it is over MAX_CONNECTIONS.'
                    );
                    return;
                }

                $connections = $channel->length();
                $this->logger->info($channel->length() . ' connections currently.');

                $streamClass = $this->clientStreamClass;
                $stream = fopen('php://memory', 'rw');
                fwrite($stream, $data);
                rewind($stream);
                $clientStream = new $streamClass(
                    $server,
                    $stream,
                    $fd,
                    $reactorId
                );
                $channel->push($clientStream);

                $instantiationClientClassName = $this->getInstantiationClientClassName();
                (new $instantiationClientClassName(
                    $clientStream,
                    $this->channels,
                    $this->synchronizers,
                    $this->procedures
                ))->start();
            }
        );
    }

    public function run(): void
    {
    }

}
