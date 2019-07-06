<?php
namespace Magnolia\Traits;

use Magnolia\Contract\APIContentsInterface;

trait CookieUsable
{
    protected $cookies = [];

    public function getCookies(): array
    {
        return $this->cookies;
    }

    public function addCookie(string $name, string $value, int $expires, string $path = '/', bool $httpOnly = false): APIContentsInterface
    {
        $this->cookies[$name] = [
            'value' => $value,
            'expires' => $expires,
            'path' => $path,
            'httpOnly' => $httpOnly,
        ];
        return $this;
    }

    public function buildCookies(): array
    {
        $data = [];
        foreach ($this->cookies as $name => $cookie) {
            $data[] = $name . '=' . $cookie['value']
                    . '; Path=' . $cookie['path']
                    . '; Expires=' . date('D, d m Y H:i:s', time() + $cookie['expires']) . ' GMT';
        }
        return ['Set-Cookie' => $data];
    }
}
