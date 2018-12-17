<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-3-3
 * Time: 下午10:11
 */

namespace Feeler\Fl;

class Number{
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

	public static function itIs($number){
		if(!is_int($number) && !is_float($number)){
			return false;
		}

		return true;
	}

	public static function isInteric($number){
		if(!is_numeric($number)){
			return false;
		}

		if(strpos((string)$number, ".") !== false){
			return false;
		}

		return true;
	}

	public static function isFloaric($number){
		if(!is_numeric($number)){
			return false;
		}

		if(strpos((string)$number, ".") === false){
			return false;
		}

		return true;
	}

	public static function isInt($number){
		return is_int($number) ? true : false;
	}

	public static function isFloat($number){
		return is_float($number) ? true : false;
	}

	public static function isUnsignedNumeric($number){
		if(!self::isNumeric($number)){
			return false;
		}

		if(strpos((string)$number, "-") !== false && $number != 0){
			return false;
		}

		return true;
	}

	public static function isUnsignedInteric($number){
		if(!self::isInteric($number)){
			return false;
		}

		if(strpos((string)$number, "-") !== false && $number != 0){
			return false;
		}

		return true;
	}

	public static function isUnsignedFloaric($number){
		if(!self::isFloaric($number)){
			return false;
		}

		if(strpos((string)$number, "-") !== false && $number != 0){
			return false;
		}

		return true;
	}

	public static function isUnsignedInt($number){
		if(!self::isInt($number)){
			return false;
		}

		if(strpos((string)$number, "-") !== false && $number !== 0){
			return false;
		}

		return true;
	}

	public static function isUnsignedFloat($number){
		if(!self::isFloat($number)){
			return false;
		}

		if(strpos((string)$number, "-") !== false && $number !== 0){
			return false;
		}

		return true;
	}

	public static function isNumeric($number){
		return is_numeric($number);
	}

	public static function isMinusNumeric($number){
		if(!self::isNumeric($number)){
			return false;
		}

		return self::isUnsignedNumeric($number) ? false : true;
	}

	public function isMinusInteric($number){
		if(!self::isInteric($number)){
			return false;
		}

		return self::isUnsignedInteric($number) ? false : true;
	}

	public function isMinusFloaric($number){
		if(!self::isFloaric($number)){
			return false;
		}

		return self::isUnsignedFloaric($number) ? false : true;
	}

	public static function isMinusInt($number){
		if(!self::isInt($number)){
			return false;
		}

		return self::isUnsignedInt($number) ? false : true;
	}

	public static function isMinusFloat($number){
		if(!self::isFloat($number)){
			return false;
		}

		return self::isUnsignedFloat($number) ? false : true;
	}

	public static function isPosiNumeric($number){
		if(!self::isNumeric($number)){
			return false;
		}

		return $number > 0 ? true : false;
	}

	public static function isPosiInteric($number){
		if(!self::isInteric($number)){
			return false;
		}

		return $number > 0 ? true : false;
	}

	public static function isPosiFloaric($number){
		if(!self::isFloaric($number)){
			return false;
		}

		return $number > 0 ? true : false;
	}

	public static function isPosiInt($number){
		if(!self::isInt($number)){
			return false;
		}

		return $number > 0 ? true : false;
	}

	public static function isPosiFloat($number){
		if(!self::isFloat($number)){
			return false;
		}

		return $number > 0 ? true : false;
	}

	public static function abs($number){
		if(!self::isNumeric($number)){
			return 0;
		}

		$number = abs($number);

		if(!self::isNumeric($number)){
			return 0;
		}

		return $number;
	}

	public static function format($number, $decimalPlaceLen = 2, $round = true, $fixedDecimalPlace = false, $showThousandsSep = false){
		if(!self::isNumeric($number) || $number == 0 || !self::isInt($decimalPlaceLen) || $decimalPlaceLen < 0){
            if($fixedDecimalPlace && self::isPosiInteric($decimalPlaceLen)){
                return "0.".str_repeat("0", $decimalPlaceLen);

            }
            else{
                return 0;
            }
		}

		if($round){
			if($showThousandsSep){
				$thousandsSep = ",";
			}
			else{
				$thousandsSep = "";
			}

			$number = (float)number_format($number, $decimalPlaceLen, ".", $thousandsSep);
		}
		else{
			if($decimalPlaceLen == 0){

			}
			else{
				$format1 = "%.{$decimalPlaceLen}f";
				$format2 = "%.".($decimalPlaceLen + 1)."f";
				$length = -$decimalPlaceLen;

				$number = sprintf($format1,substr(sprintf($format2, $number), 0, $length));
				if($showThousandsSep){
					preg_match("/(-|+)?(\d+)(\.)?(\d*)/i", $number, $matches);
					$symbol = $matches[1];
					$integerPlace = $matches[2];
					$point = $matches[3];
					$decimalPlace = $matches[4];

					if(($integerLength = strlen($integerPlace)) > 3){
						$integerPlace = strrev($integerPlace);

						$integer = "";
						for($i = 0; $i < $integerLength; $i++){
							if($i > 0 && $i % 3 == 0){
								$integer .= ",";
							}

							$integer .= $integerPlace[$i];
						}

						$integer = strrev($integer);

						$number = (float)($symbol.$integer.$point.$decimalPlace);
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