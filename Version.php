<?php
/**
 * Foundation Library
 *
 * Brief: Filting Constructions
 * Author: Rick Guo
 */

namespace Feeler\Fl;

use Feeler\Base\Str;

class Version{
    /**
     * @param $number1
     * @param $number2
     * @param $operator
     * @return bool
     * @throws \Exception
     */
	public static function compare($number1, $number2, $operator){
		if(!in_array($operator, [">", "<", "=", ">=", "<="])){
			throw new \Exception("Wrong Operator");
		}

		if(!self::isVerNum($number1) || !self::isVerNum($number2)){
			return false;
		}

		$compare = strnatcasecmp($number1, $number2);

		switch($operator){
			case ">":
				return $compare > 0 ? true : false;
				break;

			case "<":
				return $compare < 0 ? true : false;
				break;

			case "=":
				return $compare == 0 ? true : false;
				break;

			case ">=":
				return $compare >= 0 ? true : false;
				break;

			case "<=":
				return $compare <= 0 ? true : false;
				break;
		}
	}

	public static function isVerNum(&$verNum){
	    if(!Str::isAvailable($verNum)){
	        return false;
        }

	    $isVerNum = preg_match("/^v?([0-9]+(?:\.[0-9]+)*)$/i", $verNum, $matches) ? true : false;

	    if(strtolower(substr($verNum, 0, 1)) === "v"){
            $verNum = $matches[1];
        }

	    return $isVerNum;
    }
}
