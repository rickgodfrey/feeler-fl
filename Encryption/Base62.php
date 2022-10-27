<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Encryption;

use Feeler\Base\Str;

class Base62 {
    const CHARACTERS = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/";

    public static function encode(string $string):string|false{
        $rs = "";
        for($i = floor(log10($string) / log10(62)); $i >= 0; $i--) {
            $j = floor($string / pow(62, $i));
            $rs .= substr(self::CHARACTERS, $j, 1);
            $string = $string - ($j * pow(62, $i));
        }

        return $rs;
    }

    public static function decode(string $string):string|false{
        if(!Str::isAvailable($string)){
            return false;
        }
        $position = 0;
        $len = strlen($string) - 1;
        for($i = 0; $i <= $len; $i++) {
            $position += strpos(self::CHARACTERS, substr($string, $i, 1)) * pow(62, $len - $i);
        }

        return substr(sprintf("%f", $position), 0, -7);
    }
}