<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Base\Arr;
use Feeler\Base\Number;
use Feeler\Base\Str;
use Feeler\Fl\Hardware\NetworkCard;
use Feeler\Fl\System\Process;

class Random{
    const UUID_ZONE_FLAG = "7eb4014b7da8e2ffcbaec069a5b6c87c";

    const STRING = "STRING";
    const NUMBER = "NUMBER";
    const LETTERS = "LETTERS";

    public static function uuid(bool $whole = false): string {
        $uuid = self::UUID_ZONE_FLAG;
        if($macAddr = NetworkCard::getEth0MacAddr()){$uuid .= "::".md5($macAddr);}
        if($pid = Process::pid()){$uuid .= "::".md5($pid);}
        $uuid .= "::".md5(self::string(128, false, false));
        $uuid .= "::".self::uniqueId();
        $uuid = strtolower(substr(sha1($uuid), 0, 32));
        if($whole){$uuid = substr($uuid, 0, 8) ."-".substr($uuid, 8, 4) ."-".substr($uuid, 12, 4) ."-".substr($uuid, 16, 4) ."-".substr($uuid, 20, 12);}
        return $uuid;
    }

    /**
     * @param $length
     * @param bool $isNumeric
     * @param bool $withUUID
     * @return string
     */
    public static function string($length, bool $isNumeric = false, bool $withUUID = true) :string {
        if(!Number::isPosiInteric($length)){return "";}
        $length = (int)$length;
        $hash = "";
        $seed = "";
        $max = 0;
        while($max < $length){
            $str = base_convert(str_shuffle(($withUUID ? self::uuid() : self::uniqueId())), 16, ($isNumeric ? 10 : 35));
            $seed .= $str;
            $max += strlen($str);
        }
        for(($max--) && $i = 0; $i < $length; $i++) {
            $char = $seed[mt_rand(0, $max)];
            if(!$isNumeric && ($ord = ord($char)) >= 48 && $ord <= 57){
                $char = chr(mt_rand(65, 122));
            }
            $hash .= $char;
        }
        return $hash;
    }

    /**
     * @param $length
     * @return string
     */
    public static function number($length, bool $strict = true, $startWith = null) :string {
        return ($number = self::_number($length, $strict, $startWith)) ? $number
            : ((($strict = ((int)$startWith === 0 ? false : $strict)) !== null && Number::isInteric($startWith) && ($startWith = (int)$startWith) !== null && $startWith >= 0 && $startWith <= 9 && ($numbers = str_replace("{$startWith}", "", "0123456789")))
                ? (preg_match("/^[{$numbers}]*({$startWith}[0-9]*)$/", ($number = self::number($length, $strict)), $matches) && ($number = (int)$matches[1]) && (($len = ($number = (int)$number) === 0 ? 0 : strlen($number = (string)$number)) ? ($len < $length ? $number.self::number($length - $len, false) : $number) : self::number($length, $strict, $startWith)))
                : (($number = self::string($length, true)) !== null && $strict && ($number = (int)$number) !== null ? (($len = ($number = (int)$number) === 0 ? 0 : strlen($number = (string)$number)) < $length ? $number.self::string(($length - $len), true) : $number) : $number));
    }

    public static function uniqueId() :string {
        return md5(uniqid(mt_rand((int)((double)Time::microSecond() * 100000000), (int)9223372036854775807), true));
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
