<?php
namespace Magnolia\Stream;

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
}