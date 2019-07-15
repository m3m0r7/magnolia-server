<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Enum\Runtime;
use Magnolia\Exception\FileNotFoundException;
use Magnolia\Utility\Storage;

final class Capture extends AbstractAPIContents implements APIContentsInterface
{
    use \Magnolia\Traits\ImageRenderable;

    public function getResponseBody(): array
    {
        if (!$this->getSession()->has('user')) {
            return $this->returnUnauthorized(
                'You did not logged-in.'
            );
        }

        $data = null;
        $metaData = null;

        try {
            [ $data, $metaData ] = Storage::get("/record/image.jpg");
        } catch (FileNotFoundException $e) {
            $data = $this->renderBlackScreen();;
        }

        return $this->returnOK([
            'image' => 'data:image/jpeg;base64,' . base64_encode($data),
            'updated_at' => $metaData['updated_at'] ?? null,
            'update_interval' => Runtime::UPDATE_IMAGE_INTERVAL,
            'next_update' => $metaData['next_update'] ?? (time() + Runtime::UPDATE_IMAGE_INTERVAL),
        ]);
    }
}
