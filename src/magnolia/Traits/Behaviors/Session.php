<?php
namespace Magnolia\Traits\Behaviors;

use Magnolia\Client\API\Contents\AbstractAPIContents;
use Magnolia\Contract\APIContentsInterface;

class Session
{
    protected $APIContents;
    protected $sessionId = null;
    protected $sessions = [];
    protected $handle;

    public function __construct(AbstractAPIContents $APIContents)
    {
        $this->APIContents = $APIContents;
    }

    public function enable(): self
    {
        $this->APIContents->addCookie(
            getenv('SESSION_ID'),
            $this->getId(),
            getenv('SESSION_EXPIRES')
        );

        // Expand session
        $sessionFile = sys_get_temp_dir() . '/' . $this->getId();
        $this->handle = fopen($sessionFile, 'c+');

        if (flock($this->handle, LOCK_EX)) {
            $data = stream_get_contents($this->handle);
            if (!empty($data)) {
                $this->sessions = unserialize($data);
            }
            rewind($this->handle);
            ftruncate($this->handle, strlen($data));
            flock($this->handle, LOCK_UN);
        }

        return $this;
    }

    public function emit()
    {
        if (flock($this->handle, LOCK_EX)) {
            rewind($this->handle);
            fwrite(
                $this->handle,
                serialize($this->sessions)
            );
            flock($this->handle, LOCK_UN);
        }
    }

    public function write($key, $value): self
    {
        $this->sessions[$key] = $value;
        return $this;
    }

    public function read($key)
    {
        return $this->sessions[$key] ?? null;
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->sessions);
    }

    public function getId(): string
    {
        $cookies = $this->APIContents->getCookies();
        $this->sessionId = $cookies[getenv('SESSION_ID')] ?? null;
        if ($this->sessionId === null) {
            $this->sessionId = hash('sha512', getenv('SALT_KEY') . microtime(true));
        }

        return $this->sessionId;
    }
}