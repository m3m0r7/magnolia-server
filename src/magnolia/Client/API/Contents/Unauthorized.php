<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Traits\APIResponseable;
use Magnolia\Traits\CookieUsable;
use Magnolia\Traits\SessionUsable;

final class Unauthorized extends AbstractAPIContents implements APIContentsInterface
{
    public function getResponseBody(): array
    {
        return $this->returnUnauthorized(
            'Unauthorized.'
        );
    }
}
