<?php
namespace Magnolia\Traits;

use Magnolia\Enum\SynchronizerKeys;
use Magnolia\Stream\Stream;
use Magnolia\Synchronization\Synchronizer;
use Swoole\Coroutine\Channel;

trait ClientManageable
{
    public function disconnect(): void
    {
        foreach ($this->channels as $channel) {
            /**
             * @var Channel $channel
             */
            $clients = [];
            while (!$channel->isEmpty()) {
                /**
                 * @var Stream $client
                 */
                $client = $channel->pop();
                if ($client === $this->client || $client->isDisconnected()) {
                    $this->logger->info(
                        'Disconnected',
                        [$client->getPeer()]
                    );
                    break;
                }
                $clients[] = $client;
            }

            // Re-push clients.
            foreach ($clients as $client) {
                $channel->push($client);
            }
        }
        $this->client->close();
    }

    public function emit(string $data)
    {
        // Enable Buffer
        $this->client->enableBuffer(true);

        // Write headers section.
        $this->client
            ->writeLine("HTTP/1.1 200 OK")
            ->writeLine("Content-Type: application/json")
            ->writeLine("Content-Length: " . strlen($data))
            ->writeLine("");

        // Write body sections.
        $this->client
            ->write($data);

        // Emit
        $this->client->emit();

        // Close connection
        $this->disconnect();
    }
}
