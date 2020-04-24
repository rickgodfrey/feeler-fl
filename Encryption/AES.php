<?php

namespace Feeler\Fl\Encryption;

class AES {
    const AES_256_CBC = "AES-256-CBC";
    const IV = "AJhHaZ6";

    public static function encrypt(string $text, string $key) : string
    {
        return base64_encode(openssl_encrypt($text, self::AES_256_CBC, $key, OPENSSL_RAW_DATA, self::IV));
    }

    public static function decrypt(string $encode, string $key) : string
    {
        return openssl_decrypt(base64_decode($encode), self::AES_256_CBC, $key, OPENSSL_RAW_DATA, self::IV);
    }
}