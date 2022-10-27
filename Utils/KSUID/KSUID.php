<?php
namespace Feeler\Fl\Utils\KSUID;

use Feeler\Fl\Encryption\Base62;
use InvalidArgumentException;

class KSUID
{
    const TIMESTAMP_SIZE = 4;
    const PAYLOAD_SIZE = 16;
    const ENCODED_SIZE = 27;
    const EPOCH = 1400000000;

    private $timestamp;
    private $payload;

    public function __construct(int $timestamp = null, string $payload = null)
    {
        if ($payload && self::PAYLOAD_SIZE !== strlen($payload)) {
            throw new InvalidArgumentException(
                sprintf("Payload must be exactly %d bytes", self::PAYLOAD_SIZE)
            );
        }

        $this->payload = $payload;
        $this->timestamp = $timestamp;

        if (null === $payload) {
            $this->payload = random_bytes(self::PAYLOAD_SIZE);
        }
        if (null === $timestamp) {
            $this->timestamp = time() - self::EPOCH;
        }
    }

    public function bytes(): string
    {
        return pack("N", $this->timestamp) . $this->payload;
    }

    public function string(): string
    {
        $encodedUid = Base62::encode($this->bytes());
        return str_pad($encodedUid, self::ENCODED_SIZE, "0", STR_PAD_LEFT);
    }

    public function payload(): string
    {
        return (string) $this->payload;
    }

    public function timestamp(): int
    {
        return (int) $this->timestamp;
    }

    public function unixtime(): int
    {
        return $this->timestamp + self::EPOCH;
    }

    public function __toString(): string
    {
        return $this->string();
    }
}