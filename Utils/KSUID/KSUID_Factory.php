<?php
namespace Feeler\Fl\Utils\KSUID;

use Feeler\Fl\Encryption\Base62;

class KSUID_Factory
{
    public static function create(): Ksuid
    {
        return new Ksuid;
    }

    public static function fromTimestamp(int $timestamp): Ksuid
    {
        return new Ksuid($timestamp);
    }

    public static function fromUnixtime(int $unixtime): Ksuid
    {
        $timestamp = $unixtime - Ksuid::EPOCH;
        return new Ksuid($timestamp);
    }

    public static function fromTimestampAndPayload(int $timestamp, string $payload): Ksuid
    {
        return new Ksuid($timestamp, $payload);
    }

    public static function fromString(string $string): Ksuid
    {
        $decoded = Base62::decode($string);
        return self::fromBytes($decoded);
    }

    public static function fromBytes(string $bytes): Ksuid
    {
        $timestamp = substr($bytes, 0, -Ksuid::PAYLOAD_SIZE);
        $timestamp = substr($timestamp, -Ksuid::TIMESTAMP_SIZE);
        /* TODO: Array cast is a kludgle to make PHPStan happy. */
        $timestamp = (array)unpack("Nuint", $timestamp);

        $payload = substr($bytes, -Ksuid::PAYLOAD_SIZE);

        return new Ksuid($timestamp["uint"], $payload);
    }
}