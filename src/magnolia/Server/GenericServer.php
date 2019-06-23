<?php
namespace Magnolia\Server;

use Magnolia\Client\ClientInterface;
use Magnolia\Exception\ServerInterruptException;
use Magnolia\Stream\Stream;
use Monolog\Logger;

class GenericServer extends AbstractServer implements ServerInterface
{
    /**
     * @throws ServerInterruptException
     */
    public function run(): void
    {
        $clientClassName = $this->getClientClassName();

        while (true) {
            $server = stream_socket_server(
                sprintf(
                    'tcp://%s:%d',
                    $this->getListenHost(),
                    $this->getListenPort(),
                )
            );

            if ($server === false) {
                throw new ServerInterruptException('Failed to start server.');
            }

            $this->logger->info(
                'Server is running.',
                "{$this->getListenHost()}:{$this->getListenPort()}"
            );

            while (true) {
                $this->logger->info('Listening started.');
                while ($client = @stream_socket_accept($server)) {
                    // In one case, Google Chrome send 2 connections (pre-flight and fetching document data).
                    // So, It need asynchronously processing.
                    go([new $clientClassName(new Stream($client)), 'start']);
                }
            }
        }
    }

}
