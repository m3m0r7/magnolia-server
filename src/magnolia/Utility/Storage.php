<?php
namespace Magnolia\Utility;

use Magnolia\Exception\FileNotFoundException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

final class Storage
{
    public static function getPath(string $path)
    {
        $path = str_replace('..', '', $path);
        return STORAGE_DIR . '/' . ltrim($path, '/');
    }

    public static function copy(string $from, string $to)
    {

    }

    public static function put(string $path, string $content, array $meta = []): void
    {
        $path = static::getPath($path);
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
        fclose($handle);

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
        $path = static::getPath($path);

        if (!is_file($path)) {
            throw new FileNotFoundException(
                'File not found.'
            );
        }

        $handle = fopen($path, 'r');
        $metaHandle = fopen($path . '.meta.json', 'r');
        $data = '';
        $metaData = [];
        if (flock($handle, LOCK_SH)) {
            $data = stream_get_contents($handle);
            flock($handle, LOCK_UN);
        }
        fclose($handle);

        if (flock($metaHandle, LOCK_SH)) {
            $metaData = json_decode(stream_get_contents($metaHandle), true);
            flock($metaHandle, LOCK_UN);
        }

        fclose($metaHandle);

        return [ $data, $metaData ];
    }
}