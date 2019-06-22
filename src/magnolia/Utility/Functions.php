<?php
namespace Magnolia\Utility;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Functions
{
    public static function getLogger($channelName, $logLevel)
    {
        $logger = new Logger($channelName);
        $logger->pushHandler(
            new StreamHandler(
                getenv('STDOUT'),
                $logLevel
            )
        );
        return $logger;
    }

    public static function dump(...$texts)
    {
        static $handle = null;
        if ($handle === null) {
            $handle = fopen('/dev/stderr', 'w');
        }
        foreach ($texts as $text) {
            fwrite(
                $handle,
                $text
            );
        }
    }
}