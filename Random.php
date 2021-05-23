<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Base\BaseClass;
use Feeler\Base\Number;
use Feeler\Fl\Utils\UUID\UUID_Generator;

class Random extends BaseClass{
    const UUID_ZONE_FLAG = "7eb4014b7da8e2ffcbaec069a5b6c87c";
    const STRING_MIXED = "string_mixed";
    const STRING_NUMERIC = "string_numeric";
    const STRING_LETTERS = "string_letters";

    public static function uuid(string $uuidVersion = UUID_Generator::V2, bool $whole = false): string {
        return UUID_Generator::instance(self::UUID_ZONE_FLAG, [$uuidVersion, self::UUID_ZONE_FLAG, $whole])->uuidString();
    }

    public static function uniqueId() :string {
        return strtolower(substr(sha1(uniqid(random_int((int)(Time::secondInMicro() * 100000000), Number::intMaximum()), true)), 0, 32));
    }

    /**
     * @param $length
     * @param string $stringType
     * @param bool $withUUID
     * @return string
     * @throws \Exception
     */
    public static function chars($length, string $stringType = self::STRING_MIXED, bool $withUUID = true) :string {
        if(!self::defined($stringType) || !Number::isPosiInteric($length)){return "";}
        $length = (int)$length;
        $hash = "";
        $seed = "";
        $max = 0;
        while($max < $length){
            $str = str_shuffle(($withUUID ? self::uuid() : self::uniqueId()));
            ($stringType === self::STRING_NUMERIC) and ($str = base_convert($str, 16, 10));
            $seed .= $str;
            $max += strlen($str);
        }
        for(($max--) && $i = 0; $i < $length; $i++) {
            $char = $seed[random_int(0, $max)];
            if(($stringType === self::STRING_LETTERS) && ($ord = ord($char)) >= 48 && $ord <= 57){
                $char = chr(random_int(65, 122));
            }
            $hash .= $char;
        }
        return $hash;
    }

    /**
     * @param $length
     * @param bool $strict
     * @param null $startWith
     * @return string
     * @throws \Exception
     */
    public static function number($length, bool $strict = true, $startWith = null) :string {
        return ($number = self::_number($length, $strict, $startWith)) ? $number
            : ((($strict = ((int)$startWith === 0 ? false : $strict)) !== null && Number::isInteric($startWith) && ($startWith = (int)$startWith) !== null && $startWith >= 0 && $startWith <= 9 && ($numbers = str_replace("{$startWith}", "", "0123456789")))
                ? (preg_match("/^[{$numbers}]*({$startWith}[0-9]*)$/", ($number = self::number($length, $strict)), $matches) && ($number = (int)$matches[1]) && (($len = ($number = (int)$number) === 0 ? 0 : strlen($number = (string)$number)) ? ($len < $length ? $number.self::number($length - $len, false) : $number) : self::number($length, $strict, $startWith)))
                : (($number = self::chars($length, self::STRING_NUMERIC)) !== null && $strict && ($number = (int)$number) !== null ? (($len = ($number = (int)$number) === 0 ? 0 : strlen($number = (string)$number)) < $length ? $number.self::chars(($length - $len), self::STRING_NUMERIC) : $number) : $number));
    }

    private static function _number($length, bool $strict = true, $startWith = null) :string {
        if(!Number::isPosiInteric($length) || ($length = (int)$length) > 18){return "";}
        if(Number::isInteric($startWith) && ($startWith = (int)$startWith) !== null && $startWith >= 0 && $startWith <= 9 && ($startWith = (string)$startWith) !== null){if($length === 1){return $startWith;}}else{$startWith = "";}
        $max = (int)str_repeat("9", $length);
        if($strict){$min = (int)("1".str_repeat("0", $length - 1));$number = (string)mt_rand($min, $max);if(($len = ($length - strlen($number))) > 0){$number = str_repeat("0", $len).$number;}}else{$min = 0;$number = (string)mt_rand($min, $max);}
        $number = $startWith.$number;
        if($length > strlen($number)){$number = substr($number, 0, ($length - 1));}
        return $number;
    }
}
