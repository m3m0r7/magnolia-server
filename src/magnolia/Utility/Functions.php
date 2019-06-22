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
}