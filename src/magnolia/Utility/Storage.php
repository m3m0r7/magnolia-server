<?php
namespace Magnolia\Utility;

use Magnolia\Exception\FileNotFoundException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class Storage
{
    public static function put(string $path, string $content, array $meta): void
    {
        $path = STORAGE_DIR . '/' . ltrim($path, '/');
        $dirname = dirname($path);
        if (!is_dir($dirname)) {
            @mkdir($dirname, 0777, true);
        }

        $handle = fopen($path, 'w+');
        if (flock($handle, LOCK_EX)) {
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, $content);
            flock($handle, LOCK_UN);
        }

        $handle = fopen($path . '.meta.json', 'w+');
        if (flock($handle, LOCK_EX)) {
            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($meta));
            flock($handle, LOCK_UN);
        }

        fclose($handle);
    }

    public static function get(string $path): array
    {
        if (!is_file($path)) {
            throw new FileNotFoundException(
                'File not found.'
            );
        }
        $handle = fopen($path, 'c+');
        $metaHandle = fopen($path . '.meta.json', 'c+');
        $data = '';
        $metaData = [];
        if (flock($handle, LOCK_EX)) {
            $data = stream_get_contents($handle);
            flock($handle, LOCK_UN);
        }

        if (flock($metaHandle, LOCK_EX)) {
            $data = stream_get_contents($metaHandle);
            $metaData = json_decode($data, true);
            flock($handle, LOCK_UN);
        }

        return [ $data, $metaData ];
    }
}