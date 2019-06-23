<?php
namespace Magnolia\Timer;

use Magnolia\Contract\TimerInterface;
use Magnolia\Stream\Stream;
use Swoole\Coroutine\Channel;

final class ClientConnectionsCollector extends AbstractTimer implements TimerInterface
{
    protected $loggerChannelName = 'ClientConnectionsCollector';

    public static function getIntervalTime(): int
    {
        return (int) getenv('CONNECTIONS_COLLECTOR_INTERVAL_TIME');
    }

    public function run(): void
    {
        $collected = 0;

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

                // Client is disconnected.
                if ($client->isDisconnected()) {
                    $collected++;
                    continue;
                }
                $clients[] = $client;
            }

            // Re-push clients.
            foreach ($clients as $client) {
                $channel->push($client);
            }
        }

        if ($collected > 0) {
            $this->logger->info(
                'Collected disconnected clients.',
                [$collected]
            );
        }
    }
}