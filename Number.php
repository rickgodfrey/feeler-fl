<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Fl\Exceptions\AppException;

class Number extends \Feeler\Base\Number {
	public static $reflectInstance;
	protected static $numbersAndZhNumbersRelations = [
		0 => "零",
		1 => "一",
		2 => "二",
		3 => "三",
		4 => "四",
		5 => "五",
		6 => "六",
		7 => "七",
		8 => "八",
		9 => "九",
	];

    /**
     * @param $methodName
     * @param $params
     * @return bool|void
     * @throws AppException
     * @throws \ReflectionException
     */
	public static function __callStatic($methodName, $params)
	{
		if(empty($params)){
			return false;
		}

		$matched = preg_match("/^areAll(.+)$/i", $methodName, $matches);

		if(!$matched){
			return false;
		}

		$methodName = "is{$matches[1]}";

		if(!is_object(self::$reflectInstance)){
			self::$reflectInstance = new \ReflectionClass(__CLASS__);
		}

		if(!self::$reflectInstance->hasMethod($methodName) || !self::$reflectInstance->getMethod($methodName)->isStatic()){
			throw new AppException("1", "unexists overloaded method");
		}

		foreach($params as $number){
			if(!call_user_func([__CLASS__, $methodName], $number)){
				return false;
			}
		}

		return true;
	}

    public static function format($number, $decimalPlaceLen = 2, $round = true, $fixedDecimalPlace = false, $showThousandsSep = false){
        if(!self::isNumeric($number) || $number == 0 || !self::isInt($decimalPlaceLen) || $decimalPlaceLen < 0){
            if($fixedDecimalPlace && self::isPosiInteric($decimalPlaceLen)){
                return "0.".str_repeat("0", $decimalPlaceLen);
            }
            else{
                return "0";
            }
        }

        if($showThousandsSep){
            $thousandsSep = ",";
        }
        else{
            $thousandsSep = "";
        }

        if($round){
            $number = sprintf("%.{$decimalPlaceLen}f", number_format($number, $decimalPlaceLen, ".", $thousandsSep));
        }
        else{
            if($decimalPlaceLen == 0){
                $number = floor($number);
            }
            else{
                $digit = $decimalPlaceLen + 1;
                $number = sprintf("%.{$digit}f", number_format($number, $digit, ".", $thousandsSep));
                if(self::isFloaric($number)){
                    $numberParts = explode(".", $number);
                    $decimalLen = strlen($numberParts[1]);
                    if($decimalLen > $decimalPlaceLen){
                        $numberParts[1] = substr($numberParts[1], 0, ($decimalLen - ($decimalLen - $decimalPlaceLen)));
                        $number = $numberParts[0].".".$numberParts[1];
                    }
                }
            }
        }

        if($fixedDecimalPlace && self::isPosiInteric($decimalPlaceLen)){
            $numberParts = explode(".", (string)$number, 2);

            if(isset($numberParts[1])){
                if(($len = strlen($numberParts[1])) < $decimalPlaceLen){
                    $difference = $decimalPlaceLen - $len;
                    $number = $numberParts[0].".{$numberParts[1]}".str_repeat("0", $difference);
                }
            }
            else{
                $number = $number.".".str_repeat("0", $decimalPlaceLen);
            }
        }

        return $number;
    }

    /**
     * @param $number1
     * @param $number2
     * @param $operator
     * @return bool
     * @throws AppException
     */
	public static function compare($number1, $number2, $operator){
		if(!in_array($operator, [">", "<", "=", ">=", "<="])){
			throw new AppException(1, "Wrong Operator");
		}

		if(!self::isNumeric($number1) || !self::isNumeric($number2)){
			throw new AppException(1, "Wrong Params");
		}

        if(self::isInteric($number1)){
		    $number1 = (int)$number1;
        }
        else{
            $number1 = (float)$number1;
        }

        if(self::isInteric($number2)){
            $number2 = (int)$number2;
        }
        else{
            $number2 = (float)$number2;
        }

		switch($operator){
			case ">":
				return ($number1 > $number2);
				break;

			case "<":
				return ($number1 < $number2);
				break;

			case "=":
				return ($number1 == $number2);
				break;

			case ">=":
				return ($number1 >= $number2);
				break;

			case "<=":
				return ($number1 <= $number2);
				break;
		}
	}
}