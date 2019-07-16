<?php
namespace Magnolia\Client\API\Contents;

use Magnolia\Contract\APIContentsInterface;
use Magnolia\Enum\ProcedureKeys;
use Magnolia\Enum\Runtime;
use Magnolia\Traits\ProcedureManageable;
use Magnolia\Utility\Storage;
use Swoole\Coroutine\Channel;

final class Favorite extends AbstractAPIContents implements APIContentsInterface
{
    use ProcedureManageable;

    public function getResponseBody(): array
    {
        if (!$this->getSession()->has('user')) {
            return $this->returnUnauthorized(
                'You did not logged-in.'
            );
        }

        // In post case
        if ($this->method === 'POST') {
            return $this->getPostResponseBody();
        }

        $user = $this->getSession()->read('user');
        $userId = $user['id'];
        $directory = STORAGE_DIR . '/' . $userId;

        if (!is_dir($directory)) {
            mkdir($directory, 0777);
        }

        $files = [];

        foreach (glob($directory . '/*/*.meta.json') as $file) {
            $targetedDate = basename(dirname($file));
            if (!preg_match('/^(\d{4})(\d{2})$/', $targetedDate, $matches)) {
                continue;
            }
            [, $year, $month] = $matches;

            $targetedDate = $year . $month;

            if (!isset($files[$targetedDate])) {
                $files[$targetedDate] = [];
            }
            $json = json_decode(file_get_contents($file), true);

            $path = dirname($file) . '/' . $json['time'] . '.' . $json['extension'];
            $files[$targetedDate][$json['time']] = [
                'src' => '/api/v1/image?id=' . $json['time'],
            ];
        }

        // sort items
        foreach ($files as &$items) {
            krsort($items);

            $items = array_values($items);
        }

        krsort($files);

        parent::getResponseBody();
        return $this->returnOK([
            'dates' => (object) $files
        ]);
    }

    public function getPostResponseBody(): array
    {
        $this->pushToProcedureStack(
            \Magnolia\Client\Camera::class,
            ProcedureKeys::CAPTURE_FAVORITE,
            function (
                Channel $procedure,
                ?array $user,
                ...$parameters
            ) {
                $id = $user['id'];
                [ $packet ] = $parameters;
                Storage::put(
                    '/' . $id . '/' . date('Ymd') . '/' . time() . '.jpg',
                    $packet,
                    [
                        'extension' => 'jpg',
                        'time' => time(),
                        'camera_number' => 0
                    ]
                );
            },
            $this->getSession()->read('user')
        );
        return $this->returnOK();
    }
}
