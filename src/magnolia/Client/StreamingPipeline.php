<?php
namespace Magnolia\Client;

use Magnolia\Contract\ClientInterface;
use Magnolia\Enum\KindEnv;
use Magnolia\Enum\RedisKeys;
use Magnolia\Exception\WebSocketServerException;
use Magnolia\Stream\Stream;
use Magnolia\Stream\WebSocketStream;
use Magnolia\Utility\Functions;
use Magnolia\Utility\WebSocket;
use Monolog\Logger;

final class StreamingPipeline extends AbstractClient implements ClientInterface
{
    const ID = '258EAFA5-E914-47DA-95CA-C5AB0DC85B11';

    use \Magnolia\Traits\ClientManageable;
    use \Magnolia\Traits\HeaderReadable;

    /**
     * @var WebSocketStream $client
     */
    protected $client;

    protected $loggerChannelName = 'StreamingPipeline.Client';

    public function start(): void
    {
        if (!$this->proceedHeaders()) {
            return;
        }

        if (!isset($this->requestHeaders['sec-websocket-key'])) {
            $this->disconnect();
            return;
        }

        $key = $this->requestHeaders['sec-websocket-key'];

        // Write headers section.
        $this->client
            ->enableBuffer(true)
            ->writeLine("HTTP/1.1 101 Switching Protocols")
            ->writeLine('Upgrade: websocket')
            ->writeLine('Connection: upgrade')
            ->writeLine("Sec-WebSocket-Accept: " . base64_encode(sha1($key . static::ID, true)))
            ->writeLine("")
            ->emit();

        // In first, send to client which is a fulfilled black screen.
        $image = imagecreatetruecolor(640, 480);
        imagefill($image, 0, 0, imagecolorallocate($image, 0, 0, 0));
        ob_start();
        imagejpeg($image);
        $image = ob_get_clean();

        $this->client->setEstablishedHandshake(true);
        $this->client
            ->enableBuffer(false)
            ->write(
                WebSocket::encodeMessage(
                    $this->client,
                    'data:image/jpeg;base64,' . base64_encode($image),
                )
            );

        // Processing Websocket
        while (true) {
            $this->logger->info('WebSocket Receiving Server is started.');
            $readClients = [];
            $writeClients = [$this->client->getResource()];
            $expectClients = [];
            while ($changes = stream_select($readClients, $writeClients, $expectClients, 200000)) {
                if ($this->client->isDisconnected()) {
                    // Finish the coroutine processing if the client is disconnected.
                    return;
                }
                if (!empty($writeClients)) {
                    // Wrote from a client.
                    try {
                        [ $opcode, $message ] = WebSocket::decodeMessage($this->client);

                        switch ($opcode) {
                            case WebSocket::OPCODE_CLOSE:
                                $this->client
                                    ->enableBuffer(false)
                                    ->write(
                                        WebSocket::encodeMessage(
                                            $this->client,
                                            '',
                                            WebSocket::OPCODE_CLOSE
                                        )
                                    );
                                $this->disconnect();

                                // Finish the coroutine processing if the client is disconnected.
                                return;
                            case WebSocket::OPCODE_PING:
                                // If receive ping packet, then return pong packet.
                                $this->client
                                    ->enableBuffer(false)
                                    ->write(
                                        WebSocket::encodeMessage(
                                            $this->client,
                                            $message
                                        )
                                    );
                                break;
                        }

                    } catch (WebSocketServerException $e) {

                    }
                }
            }
        }
    }
}
