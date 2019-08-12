<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

class Str extends \Feeler\Base\Str {
	public static function hideParts($string, $hideRate = 0.5, $symbol = "*", $symbolRepeatTimes = 4){
		if(!self::isAvailable($string) || !is_float($hideRate) || $hideRate <= 0){
			return "";
		}

		if($hideRate >= 1){
			return $string;
		}

		$strLen = mb_strlen($string);

		if($strLen == 1){
			return $symbol;
		}

		$hideLen = ceil($strLen * $hideRate);
		$showLen = $strLen - $hideLen;

		if($symbolRepeatTimes == "AUTO"){
            $symbolRepeatTimes = $hideLen;
        }
        else if(!Number::isInteric($symbolRepeatTimes)){
            $symbolRepeatTimes = 4;
        }

        if($symbolRepeatTimes > $hideLen){
            $symbolRepeatTimes = $hideLen;
        }

        if($symbolRepeatTimes < 1){
            $symbolRepeatTimes = 1;
        }

		if($strLen == 2){
			$leftPlace = 0;
			$rightPlace = 1;
		}
		else if(Validator::testMail($string, $parts)){
			return self::hideParts($parts[1], $hideRate, $symbol, $symbolRepeatTimes)."@".$parts[2].$parts[3];
		}
		else if(Validator::testPhoneNumber($string, Validator::LOCALE_CN)){
			return mb_substr($string, 0, 3).str_repeat($symbol, $symbolRepeatTimes).mb_substr($string, 7, 4);
		}
		else{
			$leftPlace = ceil($showLen / 2);
			$rightPlace = $showLen - $leftPlace;
		}

		$stringParts = [
            mb_substr($string, 0, $leftPlace),
            mb_substr($string, $leftPlace, $hideLen),
            mb_substr($string, ($leftPlace + $hideLen))
		];

		return $stringParts[0].str_repeat($symbol, $symbolRepeatTimes).$stringParts[2];
	}

	public static function utf8Encode($string){
		if(!self::isAvailable($string)){
			return "";
		}

		$string = utf8_encode($string);
		$string = utf8_decode($string);

		return $string;
	}

    public static function mbSplit($string, $len = 1) {
        $start = 0;
        $strlen = mb_strlen($string);
        $array = [];

        while ($strlen){
            $array[] = mb_substr($string, $start, $len,"utf8");
            $string = mb_substr($string, $len, $strlen,"utf8");
            $strlen = mb_strlen($string);
        }

        return $array;
    }
}