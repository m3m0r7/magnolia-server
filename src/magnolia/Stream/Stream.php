<?php
namespace Magnolia\Stream;

use Magnolia\Utility\Functions;
use Ramsey\Uuid\Uuid;

class Stream
{
    protected $server;
    protected $stream;
    protected $fd;
    protected $reactorId;
    protected $buffers = '';
    protected $buffering = false;
    protected $peer = null;
    protected $uuid = null;
    protected $chunk = true;
    protected $chunkSize = 8192 * 2;

    public function __construct(\Swoole\Server $server, $stream, int $fd, int $reactorId)
    {
        $this->server = $server;
        $this->stream = $stream;
        $this->fd = $fd;
        $info = $server->connection_info($fd);
        $this->peer = $info['remote_ip'] . ':' . $info['remote_port'];
        $this->reactorId = $reactorId;
        $this->uuid = (string) Uuid::uuid4();
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
        return fread($this->stream, $bytes);
    }

    public function readLine(): string
    {
        return fgets($this->stream);
    }

    public function close(): void
    {
        $this->server->close($this->fd);
    }

    public function emit(): void
    {
        $this->server->send($this->fd, $this->buffers);

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