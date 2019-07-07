<?php
namespace Magnolia\Traits;

use Magnolia\Contract\APIContentsInterface;

trait CookieUsable
{
    protected $cookies = [];
    protected $setCookies = [];

    public function enableCookie(): self
    {
        foreach (preg_split('/\s*;\s*/', $this->headers['cookie'] ?? '') as $cookie) {
            $splitCookie = preg_split('/\s*=\s*/', $cookie, 2);
            if (count($splitCookie) < 2) {
                continue;
            }
            [ $key, $value ] = $splitCookie;
            $this->cookies[$key] = $value;
        }
        return $this;
    }

    public function getCookies(): array
    {
        return array_map(function ($cookie) {
            return $cookie['value'] ?? null;
        }, $this->setCookies) + $this->cookies;
    }

    public function addCookie(string $name, string $value, int $expires, string $path = '/', bool $httpOnly = false): APIContentsInterface
    {
        $this->setCookies[$name] = [
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
        foreach ($this->setCookies as $name => $cookie) {
            $data[] = $name . '=' . $cookie['value']
                    . '; Path=' . $cookie['path']
                    . '; Expires=' . date('D, d m Y H:i:s', time() + $cookie['expires']) . ' GMT';
        }
        if (empty($data)) {
            return [];
        }
        return ['Set-Cookie' => $data];
    }
}
