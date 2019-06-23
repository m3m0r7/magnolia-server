<?php
namespace Magnolia\Stream;

use Magnolia\Utility\Functions;

final class Stream
{
    protected $stream;
    protected $buffers = '';
    protected $buffering = false;

    public function __construct($stream)
    {
        stream_set_timeout($stream, 1, 0);
        stream_set_write_buffer($stream, 0);
        stream_set_read_buffer($stream, 0);

        $this->stream = $stream;
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

    public function read(int $byte = 1): string
    {
        $body = '';
        do {
            $read = fread($this->stream, $byte);
            $body .= $read;
        } while (strlen($body) < $byte);
        return $body;
    }

    public function readLine(): string
    {
        return fgets($this->stream);
    }

    public function close(): void
    {
        fclose($this->stream);
    }

    public function emit()
    {
        fwrite($this->stream, $this->buffers);

        // Do empty buffers
        $this->buffers = '';
    }
}