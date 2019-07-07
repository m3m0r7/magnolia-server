<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;

final class User extends AbstractAPIContents implements APIContentsInterface
{
    public function getResponseBody(): array
    {
        if (!$this->getSession()->has('user')) {
            return $this->returnUnauthorized(
                'Does not logged-in.'
            );
        }

        parent::getResponseBody();
        return $this->returnOK([
            'user' => $this->getSession()->read('user'),
        ]);
    }
}
