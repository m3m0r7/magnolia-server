<?php
namespace Magnolia\Stream;

use Magnolia\Utility\Functions;
use Ramsey\Uuid\Uuid;

class Stream
{
    protected $stream;
    protected $buffers = '';
    protected $buffering = false;
    protected $peer = null;
    protected $uuid = null;
    protected $chunk = true;
    protected $chunkSize = 8192;

    public function __construct($stream)
    {
        $this->stream = $stream;
        $this->peer = stream_socket_get_name(
            $stream,
            true
        );
        $this->uuid = Uuid::uuid4();
    }

    public function getPeer(): string
    {
        return $this->peer;
    }

    public function getResource()
    {
        return $this->stream;
    }

    public function enableBuffer(bool $enable): self
    {
        $this->buffering = $enable;
        return $this;
    }

    public function enableChunk(bool $enable): self
    {
        $this->chunk = $enable;
        return $this;
    }

    public function getChunkSize()
    {
        return $this->chunkSize;
    }

    public function write(string $data): self
    {
        $this->buffers .= $data;
        if (!$this->buffering) {
            $this->emit();
        }
        return $this;
    }

    public function writeLine(string $data): self
    {
        return $this->write($data . "\n");
    }

    public function read(int $bytes = 1): string
    {
        if ($bytes <= 0) {
            return '';
        }
        $remaining = $bytes;
        $data = '';
        do {
            $data .= fread($this->stream, $remaining);
            $remaining = $bytes - strlen($data);
        } while ($remaining > 0);
        return $data;
    }

    public function readLine(): string
    {
        return fgets($this->stream);
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    public function emit(): void
    {
        if ($this->chunk) {
            foreach (str_split($this->buffers, $this->chunkSize) as $chunk) {
                fwrite($this->stream, $chunk);
            }
        } else {
            fwrite($this->stream, $this->buffers);
        }

        // Do empty buffers
        $this->buffers = '';
    }

    public function isDisconnected(): bool
    {
        return feof($this->stream);
    }

    public function getUUID(): string
    {
        return $this->uuid;
    }
}