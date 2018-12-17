<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-5-12
 * Time: 下午3:04
 */

namespace rickguo\Fl;

class Str{
	public static function isAvailable(&$string){
		if(!is_string($string)){
			return false;
		}

		$string = trim($string);

		if(!$string){
			return false;
		}

		return true;
	}

	public static function itIs($string){
		return is_string($string);
	}

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
		else if(Validator::testPhoneNumber($string, $country = "CN")){
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

	public static function getFirstLetter($string){
		if(!self::isAvailable($string)){
			return false;
		}

		if(Validator::testAscii($string) && ($letter = strtolower(substr($string, 0, 1))) && preg_match("/[a-z]+/", $letter)){
			return strtoupper($letter);
		}

		$string = iconv("UTF-8","gb2312", $string);

		if (preg_match("/^[\x7f-\xff]/", $string))
		{
			$fchar=ord($string[0]);
			if($fchar>=ord("A") and $fchar<=ord("z") ){
				return strtoupper($string[0]);
			}
			$a = $string;
			$val = ord($a[0]) * 256 + ord($a[1]) - 65536;
			if($val>=-20319 and $val<=-20284) return "A";
			if($val>=-20283 and $val<=-19776) return "B";
			if($val>=-19775 and $val<=-19219) return "C";
			if($val>=-19218 and $val<=-18711) return "D";
			if($val>=-18710 and $val<=-18527) return "E";
			if($val>=-18526 and $val<=-18240) return "F";
			if($val>=-18239 and $val<=-17923) return "G";
			if($val>=-17922 and $val<=-17418) return "H";
			if($val>=-17417 and $val<=-16475) return "J";
			if($val>=-16474 and $val<=-16213) return "K";
			if($val>=-16212 and $val<=-15641) return "L";
			if($val>=-15640 and $val<=-15166) return "M";
			if($val>=-15165 and $val<=-14923) return "N";
			if($val>=-14922 and $val<=-14915) return "O";
			if($val>=-14914 and $val<=-14631) return "P";
			if($val>=-14630 and $val<=-14150) return "Q";
			if($val>=-14149 and $val<=-14091) return "R";
			if($val>=-14090 and $val<=-13319) return "S";
			if($val>=-13318 and $val<=-12839) return "T";
			if($val>=-12838 and $val<=-12557) return "W";
			if($val>=-12556 and $val<=-11848) return "X";
			if($val>=-11847 and $val<=-11056) return "Y";
			if($val>=-11055 and $val<=-10247) return "Z";
		}
		else
		{
			return null;
		}
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