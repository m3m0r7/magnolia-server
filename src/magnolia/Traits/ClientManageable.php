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
                if ($client === $this->client) {
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
}
