<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-8-29
 * Time: 下午6:10
 */

namespace Fl;

class Math{
	public static function calc($pattern){
		if(!Str::isAvailable($pattern)){
			return null;
		}

		$rs = null;
		$statement = "\$rs = {$pattern};";
		eval($statement);

		return $rs;

		$operators = ['#'];

		$numbers = [];

		$priorities = [
			')' => 2,
			'(' => 3,
			'+' => 4,
			'-' => 4,
			'*' => 5,
			'/' => 5,
			'#' => 1
		];

		$mathOperators = [];

		$patternLen = strlen($pattern);
		$operatorStr = "";

		for($i = 0; $i <= $patternLen; $i++){
			$char = $pattern[$i];

			$level = intval($priorities[$char]);
			if($level > 0){
				if($level == 3){
					array_push($operators, $char);
					continue;
				}

				while($operator = array_pop($operators)){
					if($operator){
						$currentLevel = intval($priorities[$operator]);
						if($currentLevel == 3 && $level == 2) {
							break;
						}
						else if($currentLevel >= $level && $currentLevel != 3){
							array_push($mathOperators, $operator);
						}
						else{
							array_push($operators, $operator);
							array_push($operators, $char);
							break;
						}
					}
				}
			}else{
				$operatorStr .= trim($char);
				if($priorities[$pattern[$i+1]] > 0){
					array_push($mathOperators, $operatorStr);
					$operatorStr = "";
				}
			}
		}
		array_push($mathOperators, $operatorStr);

		while($leftOp = array_pop($operators)){
			if($leftOp != '#'){
				array_push($mathOperators, $leftOp);
			}
		}

		foreach($mathOperators as $v){
			$v = trim($v);
			if($v == ""){
				continue;
			}

			if(!isset($priorities[$v])){
				array_push($numbers, $v);
			}else{
				$number1 = array_pop($numbers);
				$number2 = array_pop($numbers);

				$number1 = trim($number1);
				$number2 = trim($number2);

				switch($v){
					case "+":
						if(!self::add($number2, $number1)){
							return null;
						}

						break;

					case "-":
						if(!self::sub($number2, $number1)){
							return null;
						}

						break;

					case "*":
						if(!self::mult($number2, $number1)){
							return null;
						}

						break;

					case "/":
						if(!self::divis($number2, $number1)){
							return null;
						}

						break;
				}

				array_push($numbers, $number2);
			}
		}

		return $number2;
	}

	public static function add(&$element1, $element2){
		$numbers = func_get_args();

		$element1 = 0;

		foreach($numbers as $key => $number){
			if(!Number::isNumeric($number)){
				$element1 = null;
				return false;
			}

			$element1 += $number;
		}

		return true;
	}

	public static function sub(&$element1, $element2){
		$numbers = func_get_args();

		$element1 = 0;

		foreach($numbers as $key => $number){
			if(!Number::isNumeric($number)){
				$element1 = null;
				return false;
			}

			$element1 -= $number;
		}

		return true;
	}

	public static function mult(&$element1, $element2){
		$numbers = func_get_args();

		$element1 = 1;

		foreach($numbers as $key => $number){
			if(!Number::isNumeric($number)){
				$element1 = null;
				return false;
			}

			if($number == 0){
				$element1 = 0;
				return true;
			}

			$element1 *= $number;
		}

		return true;
	}

	public static function divis(&$element1, $element2){
		$numbers = func_get_args();

		$element1 = $numbers[0];
		if(!Number::isNumeric($element1)){
			$element1 = null;
			return false;
		}

		unset($numbers[0]);
		Arr::tidy($numbers);

		foreach($numbers as $key => $number){
			if(!Number::isNumeric($number) || $number == 0){
				$element1 = null;
				return false;
			}

			$element1 /= $number;
		}

		return true;
	}
}