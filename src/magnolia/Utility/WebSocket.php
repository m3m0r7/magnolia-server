<?php
namespace Magnolia\Utility;

use Magnolia\Exception\WebSocketServerException;
use Magnolia\Stream\Stream;
use Magnolia\Stream\WebSocketStream;

final class WebSocket
{
    const OPCODE_FIN = 0x00;
    const OPCODE_MESSAGE = 0x01;
    const OPCODE_BINARY = 0x02;
    const OPCODE_CLOSE = 0x08;
    const OPCODE_PING = 0x09;
    const OPCODE_PONG = 0x0A;

    public static function decodeMessage(WebSocketStream $client): array
    {
        $byte = ord($client->read(1));

        $fin = $byte >> 7;
        if ($fin < 0 || $fin > 1) {
            throw new WebSocketServerException(
                'Fin is invalid.'
            );
        }

        $opcode = (($byte << 4) & 0xff) >> 4;

        if (!in_array(
            $opcode,
            [
                static::OPCODE_FIN,
                static::OPCODE_MESSAGE,
                static::OPCODE_BINARY,
                static::OPCODE_CLOSE,
                static::OPCODE_PING,
                static::OPCODE_PONG,
            ],
            true
        )) {
            throw new WebSocketServerException(
                'Opcode is invalid.'
            );
        }

        $byte = ord($client->read(1));

        $maskFlag = ($byte >> 7) & 0xff;
        $length = $type = (($byte << 1) & 0xff) >> 1;

        if ($type === 126) {
            $length = $client->read(2);
        } elseif ($type === 127) {
            $length = $client->read(8);
        }

        $masks = array_values(unpack('C4', $client->read(4)));
        if (count($masks) !== 4) {
            throw new WebSocketServerException(
                'Mask is not enough conditions'
            );
        }

        return [
            $opcode,
            static::mask(
                $length > 0
                    ? $client->read($length)
                    : '',
                $masks
            ),
        ];
    }

    public static function encodeMessage(WebSocketStream $client, string $payload, int $opcode = self::OPCODE_MESSAGE): string
    {
        $length = strlen($payload);
        $type = ($length > 0xffff ? 127 : ($length <= 0xffff && $length >= 126 ? 126 : $length));

        $body = '';

        // set fin already.
        $body .= chr(
            // Fin + RSV1 + RSV2 + RSV3 + opcode
            128 + $opcode
        );

        $body .= chr(
            // Mask (Not use) + payload length
            $type
        );

        if ($type === 126) {
            $body .= pack('n', $length);
        } elseif ($type === 127) {
            $body .= pack('J', $length);
        }

        $body .= $payload;

        return $body;
    }

    public static function mask(string $message, array $masks): string
    {
        for ($i = 0, $length = strlen($message); $i < $length; $i++) {
            $message[$i] = chr(ord($message[$i]) ^ $masks[$i % 4]);
        }

        return $message;
    }
}