<?php
namespace Magnolia\Traits;


use Magnolia\Stream\Stream;

/**
 * @property-read Stream $client
 */
trait HeaderReadable
{
    protected $requestHeaders = [];

    public function proceedHeaders(): bool
    {
        $readLength = 0;
        while ($line = $this->client->readLine()) {
            if (ltrim($line, "\r") === "\n") {
                break;
            }
            if ($line === '') {
                // No data.
                $this->disconnect();
                return false;
            }

            $readLength += strlen($line);
            if (((int) getenv('MAX_HEADER_LENGTH')) < $readLength) {
                $this->disconnect();
                return false;
            }

            $this->requestHeaders[] = rtrim($line, "\n");
        }

        $this->requestHeaders = $this->parseHeaders($this->requestHeaders);
        return true;
    }

    public function parseHeaders(array $headers)
    {
        $parsedHeaders = [];
        foreach ($headers as $header) {
            if (preg_match('/^(.*?):(.*)/', $header, $matches)) {
                $parsedHeaders[trim(strtolower($matches[1]))] = trim($matches[2]);
                continue;
            }
            $parsedHeaders[] = $header;
        }
        return $parsedHeaders;
    }
}