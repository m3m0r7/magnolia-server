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
        $this->logger->info(
            'Started EnvInfo receiving server.',
            [
                "{$this->getListenHost()}:{$this->getListenPort()}",
            ]
        );

        $clientClassName = $this->getClientClassName();

        while (true) {
            $this->logger->info('Instantiate a server');
            $server = stream_socket_server(
                sprintf(
                    'tcp://%s:%d',
                    $this->getListenHost(),
                    $this->getListenPort(),
                )
            );

            if ($server === false) {
                throw new ServerInterruptException('Server cannot wakeup.');
            }

            while (true) {
                $this->logger->info('Listening started.');
                while ($client = @stream_socket_accept($server)) {
                    /**
                     * @var ClientInterface $clientClass
                     */
                    $clientClass = new $clientClassName(
                        new Stream($client),
                    );
                    $clientClass->start();
                }
            }
        }
    }

}
