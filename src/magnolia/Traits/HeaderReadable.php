<?php
namespace Magnolia\Traits;

trait HeaderReadable
{
    protected $responseHeaders = [];

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

            $this->responseHeaders[] = rtrim($line, "\n");
        }
        return true;
    }
}