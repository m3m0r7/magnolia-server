<?php
namespace Magnolia\Traits\Behaviors;

use Magnolia\Client\API\Contents\AbstractAPIContents;
use Magnolia\Contract\APIContentsInterface;

class Session
{
    protected $APIContents;
    protected $sessionId = null;
    protected $sessions = [];

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
        return $this;
    }

    public function write($key, $value): self
    {
        $this->sessions[$key] = $value;
        return $this;
    }

    public function read($key): string
    {
        return $this->sessions[$key];
    }

    public function has($key): bool
    {
        return array_key_exists($key, $this->sessions);
    }

    public function getId()
    {
        $cookies = $this->APIContents->getCookies();
        $this->sessionId = $cookies[getenv('SESSION_ID')] ?? null;
        if ($this->sessionId === null) {
            $this->sessionId = hash('sha512', getenv('SALT_KEY') . microtime(true));
        }

        return $this->sessionId;
    }
}