<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Base\Number;
use Feeler\Base\Str;
use Feeler\Fl\Hardware\NetworkCard;
use Feeler\Fl\System\Process;

class Random{
    const UUID_ZONE_FLAG = "7eb4014b7da8e2ffcbaec069a5b6c87c";

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
        if($withUUID){$string = strtoupper(($string = self::uuid())).$string;}
        else{$string = strtoupper(($string = self::uniqueId())).$string;}
        $seed = base_convert(str_shuffle($string), 16, ($isNumeric ? 10 : 36));
        $max = strlen($seed) - 1;
        while($max < $length){
            $str = base_convert(str_shuffle($string), 16, ($isNumeric ? 10 : 36));
            $seed .= $str;
            $max += strlen($str);
        }
        for($i = 0; $i < $length; $i++) {
            $hash .= $seed[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * @param $length
     * @return string
     */
    public static function number($length, $startWith = null) :string {
        return (Number::isInteric($startWith) && ($startWith = (int)$startWith) !== null && $startWith >= 0 && $startWith <= 9 && ($numbers = str_replace("{$startWith}", "", "0123456789")))
            ? (preg_match("/[{$numbers}]*?({$startWith}[0-9]+)?/", ($number = self::number($length)), $matches) && Number::isInteric($matches[1]) ? (($matchedLen = strlen($matches[1])) === $length ? $matches[1] : ($matches[1].self::number($length - $matchedLen))) : self::number($length, $startWith))
            : (($len = strlen(($number = ltrim(self::string($length, true), "0")))) < $length ? $number.self::string(($length - $len), true) : $number);
    }

    public static function uniqueId() :?string {
        return md5(uniqid(mt_rand((int)((double)Time::microSecond() * 100000000), (int)9223372036854775807), true));
    }
}
