<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Encryption;

class Base64 {
    public static function encode(string $string){
        return base64_encode($string);
    }

    public static function decode(string $string){
        return base64_decode($string);
    }
}