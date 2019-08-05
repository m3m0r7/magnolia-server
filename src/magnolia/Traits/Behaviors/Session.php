<?php
namespace Magnolia\Traits\Behaviors;

use Magnolia\Client\API\Contents\AbstractAPIContents;
use Magnolia\Contract\APIContentsInterface;
use Magnolia\Enum\RedisKeys;

class Session
{
    use \Magnolia\Traits\Redis;

    protected $APIContents;
    protected $sessionId = null;
    protected $sessions = [];
    protected $handle;
    protected $lifecycle = 3600;

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

        $key = RedisKeys::SESSION . '_' .  $this->getId();

        $this->getRedis()->setnx(
            $key,
            serialize($this->sessions)
        );

        $this->sessions = unserialize(
            $this->getRedis()->get($key)
        );

        return $this;
    }

    public function emit()
    {
        $key = RedisKeys::SESSION . '_' .  $this->getId();

        $this->getRedis()->set(
            $key,
            serialize($this->sessions)
        );

        $this->getRedis()->expire($key, $this->lifecycle);
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