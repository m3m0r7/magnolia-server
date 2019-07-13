<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Exception\FileNotFoundException;
use Magnolia\Traits\APIResponseable;
use Magnolia\Traits\CookieUsable;
use Magnolia\Traits\SessionUsable;
use Magnolia\Utility\Storage;

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

        $data = '';
        $metaData = [];
        try {
            [ $data, $metaData ] = Storage::get("/{$userId}/{$date}");
            $this->setContentType($metaData['extension']);
        } catch (FileNotFoundException $e) {
            return $this->returnNotFound(
                'Image not found.'
            );
        }

        return [
            'body' => fopen($path . '/' . $metaData['time'] . '.' . $metaData['extension'], 'r'),
        ];
    }
}