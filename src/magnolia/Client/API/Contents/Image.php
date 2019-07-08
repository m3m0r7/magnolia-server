<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Traits\APIResponseable;
use Magnolia\Traits\CookieUsable;
use Magnolia\Traits\SessionUsable;

final class Image extends AbstractAPIContents implements APIContentsInterface
{
    public function getResponseBody(): array
    {
        if (!$this->getSession()->has('user')) {
            return $this->returnUnauthorized(
                'You did not logged-in.'
            );
        }
        $id = str_replace('/', '', $this->getQuery()->get('id'));
        $date = str_replace('/', '', $this->getQuery()->get('date'));

        $user = $this->getSession()->read('user');
        $userId = $user['id'];
        $path = STORAGE_DIR . '/' . $userId . '/' . $date;
        $meta = $path . '/' . $id . '.meta.json';

        if (!is_file($meta)) {
            return $this->returnNotFound(
                'Image not found.'
            );
        }

        $metaData = json_decode(
            file_get_contents($meta),
            true
        );

        $this->setContentType($metaData['extension']);

        return [
            'body' => fopen($path . '/' . $metaData['time'] . '.' . $metaData['extension'], 'r'),
        ];
    }
}