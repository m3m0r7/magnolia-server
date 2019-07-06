<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;

final class User extends AbstractAPIContents implements APIContentsInterface
{
    public function getResponseBody(): array
    {
        var_dump($this->getSession()->read('user'));
        return [];
    }
}
