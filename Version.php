<?php
/**
 * Foundation Library
 *
 * Brief: Filting Constructions
 * Author: Rick Guo
 */

namespace Fl;

class Version{
	public static function compare($number1, $number2, $operator){
		if(!in_array($operator, [">", "<", "=", ">=", "<="])){
			throw new AppException(1, "Wrong Operator");
		}

		if(Number::isNumeric($number1) && Number::isNumeric($number2)){
			return Number::compare($number1, $number2, $operator);
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
}
