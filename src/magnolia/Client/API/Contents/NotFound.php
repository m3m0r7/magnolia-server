<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Traits\APIResponseable;
use Magnolia\Traits\CookieUsable;
use Magnolia\Traits\SessionUsable;

final class NotFound extends AbstractAPIContents implements APIContentsInterface
{
    public function getResponseBody(): array
    {
        return $this->returnNotFound(
            'The API does not implemented.'
        );
    }
}