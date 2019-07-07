<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Traits\APIResponseable;
use Magnolia\Traits\CookieUsable;
use Magnolia\Traits\SessionUsable;

final class PreflightRequest extends AbstractAPIContents implements APIContentsInterface
{
    public function getResponseHeaders(): array
    {
        return [
            'Access-Control-Allow-Method' => '*',
            'Access-Control-Allow-Headers' => 'content-type',
        ] + parent::getResponseHeaders();
    }
}