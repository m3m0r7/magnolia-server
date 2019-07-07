<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;

final class Favorite extends AbstractAPIContents implements APIContentsInterface
{
    public function getResponseBody(): array
    {
        if (!$this->getSession()->has('user')) {
            return $this->returnUnauthorized(
                'You did not logged-in.'
            );
        }

        parent::getResponseBody();
        return $this->returnOK([
            '2019/12' => [
                [
                    'src' => '/img/iris.jpg',
                ],
                [
                    'src' => '/img/iris.jpg',
                ],
                [
                    'src' => '/img/iris.jpg',
                ],
                [
                    'src' => '/img/iris.jpg',
                ],
                [
                    'src' => '/img/iris.jpg',
                ],
            ]
        ]);
    }
}
