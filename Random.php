<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Base\BaseClass;
use Feeler\Base\Arr;
use Feeler\Base\Number;
use Feeler\Base\Str;
use Feeler\Fl\Hardware\NetworkCard;
use Feeler\Fl\System\Process;
use Feeler\Fl\Utils\UUID\UUID;

class Random extends BaseClass{
    const UUID_ZONE_FLAG = "7eb4014b7da8e2ffcbaec069a5b6c87c";
    const STRING_MIXED = "string_mixed";
    const STRING_NUMERIC = "string_numeric";
    const STRING_LETTERS = "string_letters";

    public static function uuid(bool $whole = false): string {
        return UUID::instance()->uuidString(UUID::V2, self::UUID_ZONE_FLAG, $whole);
    }

    public static function uniqueId() :string {
        return md5(uniqid(random_int((int)(Time::secondInMicro() * 100000000), Number::intMaximum()), true));
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
     * @return string
     * @throws \Exception
     */
    public static function number($length, bool $strict = true) :string {
        if(!Number::isPosiInteric($length)){
            return "";
        }
        $number = random_bytes((int)$length);
        if($strict && ($start = substr($number, 0, 1))){
            $number = (string)random_int(1, 9).substr($number, 1);
        }
        return $number;
    }
}
