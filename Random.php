<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Base\BaseClass;
use Feeler\Base\Number;
use Feeler\Base\Math\Utils\BasicOperation;
use Feeler\Fl\Utils\UUID\UUID_Generator;

class Random extends BaseClass{
    const UUID_ZONE_FLAG = "7bc7d4ade3240e891cd9042a0179fab6";
    const STRING_MIXED = "string_mixed";
    const STRING_NUMERIC = "string_numeric";
    const STRING_LETTERS = "string_letters";

    public static function uuid(string $uuidVersion = UUID_Generator::V2, bool $whole = false): string {
        return UUID_Generator::instance(self::UUID_ZONE_FLAG, [$uuidVersion, self::UUID_ZONE_FLAG, $whole])->uuidString();
    }

    public static function uniqueId() :string {
        return strtolower(substr(sha1(uniqid(random_int((int)(Time::secondInMicro() * 100000000), Number::intMax()), true)), 0, 32));
    }

    /**
     * @param $length
     * @param string $stringType
     * @param bool $withUUID
     * @return string
     * @throws \Exception
     */
    public static function chars(int $length, string $stringType = self::STRING_MIXED, bool $withUUID = false) :string {
        if(!self::defined($stringType) || !Number::isPosiInt($length)){return "";}
        $hash = "";
        $seed = "";
        $round = 0;
        while($round < $length){
            $str = str_shuffle(($withUUID ? self::uuid() : self::uniqueId()));
            ($stringType === self::STRING_NUMERIC) and ($str = base_convert($str, 16, 10));
            $seed .= $str;
            $round += strlen($str);
        }
        for(($round--) && $i = 0; $i < $length; $i++) {
            $char = $seed[random_int(0, $round)];
            if(($stringType === self::STRING_LETTERS) && ($ord = ord($char)) >= 48 && $ord <= 57){
                $char = chr(random_int(65, 122));
            }
            $hash .= $char;
        }
        return $hash;
    }

    public static function number(int $length, bool $strict = true) :string {
        if($length > 18){
            $asBigNumber = true;
        }
        else{
            $asBigNumber = false;
        }
        $min = $strict ? (int)("1".str_repeat("0", $length - 1)) : 0;
        $max = str_repeat("9", $length);
        $number = BasicOperation::randomInt($min, $max, $asBigNumber);
        return $number;
    }
}
