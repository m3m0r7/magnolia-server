<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Traits\APIResponseable;
use Magnolia\Traits\CookieUsable;
use Magnolia\Traits\SessionUsable;

final class Login extends AbstractAPIContents implements APIContentsInterface
{
    public function getResponseBody(): array
    {
        if ($this->method !== 'POST') {
            return $this->returnBadRequest(
                'Does not allowed method.'
            );
        }

        try {
            $content = json_decode(
                $this->content,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\Exception $e) {
            return $this->returnBadRequest(
                'Requested body is not a JSON format.'
            );
        }

        $user = $this->findUser(
            $content['username'] ?? null,
            $content['password'] ?? null,
        );

        if (empty($user)) {
            return $this->returnUnauthorized(
                'ID or Password is incorrect.'
            );
        }

        unset($user['password']);
        $this->getSession()->write('user', $user);

        parent::getResponseBody();
        return [];

    }

    private function findUser($userName, $password): array
    {
        static $userFile = null;

        $path = ROOT_DIR . '/users.json';
        $user = [];

        if ($userFile === null) {
            $userFile = [];
            if (is_file($path)) {
                $userFile = json_decode(
                    file_get_contents($path),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );
            }
        }

        if (($userFile[$userName] ?? null) === null) {
            return [];
        }

        if (($userFile[$userName]['password'] ?? null) === $password) {
            return $userFile[$userName];
        }

        return [];
    }
}
