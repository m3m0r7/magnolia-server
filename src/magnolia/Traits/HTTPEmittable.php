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
            ->writeLine("Content-Length: " . strlen($body))
            ->writeLine("Access-Control-Allow-Origin: *");

        foreach ($apiContents->getHeaders() as $header => $value) {
            $this->client
                ->writeLine($header . ': ' . $value);
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