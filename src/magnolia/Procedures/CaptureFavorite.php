<?php
namespace Magnolia\Procedures;

use Magnolia\Contract\ProcedureInterface;
use Magnolia\Utility\Storage;

class CaptureFavorite implements ProcedureInterface
{
    public function exec(...$parameters): void
    {
        [ $user, $packet ] = $parameters;
        $id = $user['id'];
        Storage::put(
            '/' . $id . '/' . date('Ymd') . '/' . time() . '.jpg',
            $packet,
            [
                'extension' => 'jpg',
                'time' => time(),
                'camera_number' => 0
            ]
        );
    }
}
