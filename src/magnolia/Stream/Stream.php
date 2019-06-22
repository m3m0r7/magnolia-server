<?php
namespace Magnolia\Stream;

use Magnolia\Utility\Functions;

final class Stream
{
    protected $stream;

    public function __construct($stream)
    {
        $this->stream = $stream;
    }

    public function getResource()
    {
        return $this->stream;
    }

    public function read(int $byte): string
    {
        $body = '';
        do {
            $read = fread($this->stream, $byte);
            $body .= $read;
        } while (strlen($body) < $byte);
        return $body;
    }
}