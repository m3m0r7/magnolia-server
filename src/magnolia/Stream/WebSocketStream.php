<?php
namespace Magnolia\Stream;

use Magnolia\Utility\Functions;
use Ramsey\Uuid\Uuid;

class WebSocketStream extends Stream
{
    protected $establishedHandshake = false;

    public function isEstablishedHandshake(): bool
    {
        return $this->establishedHandshake;
    }

    public function setEstablishedHandshake(bool $value): self
    {
        $this->establishedHandshake = $value;
        return $this;
    }
}