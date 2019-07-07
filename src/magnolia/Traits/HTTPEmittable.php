<?php
namespace Magnolia\Traits;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Stream\Stream;

/**
 * @property-read Stream $client
 */
trait HTTPEmittable
{
    public function emit(APIContentsInterface $apiContents)
    {
        // Enable Buffer
        $this->client->enableBuffer(true);

        $body = (string) $apiContents;

        // Write headers section.
        $this->client
            ->writeLine("HTTP/1.1 " . $this->stringifyStatus($apiContents->getStatus()))
            ->writeLine("Content-Type: application/json")
            ->writeLine("Content-Length: " . strlen($body));

        foreach ($apiContents->getResponseHeaders() as $header => $value) {
            if (!is_array($value)) {
                $value = [$value];
            }
            foreach ($value as $record) {
                $this->client
                    ->writeLine($header . ': ' . $record);
            }
        }

        $this->client->writeLine("");

        // Write body sections.
        $this->client
            ->write($body);

        // Emit
        $this->client->emit();

        // Close connection
        $this->disconnect();
    }

    public function stringifyStatus(int $statusCode)
    {
        switch ($statusCode) {
            case 400:
                return '400 Bad Request';
            case 401:
                return '401 Unauthorized';
            case 403:
                return '403 Forbidden';
            case 500:
                return '500 Internal Server Error';
        }
        return '200 OK';
    }
}