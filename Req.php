<?php
/**
 * Foundation Library
 *
 * Brief: Requestion Constructions
 * Author: Rick Guo
 */

namespace Feeler\Fl;

class Req{
	protected static $input;

	public static function filt($data, $type = "HTML_ESCAPED", $len = -1){
		return Filter::act($data, $type, $len);
	}

	public static function get($field = null, $type = "NO_FILTING", $len = -1){
		if($field === null){
			return self::filt($_GET, $type, $len);
		}

		$value = Arr::getVal($_GET, $field);
		$value = self::filt($value, $type, $len);
		
		return $value;
	}

	public static function post($field = null, $type = "NO_FILTING", $len = -1){
		if($field === null){
			return self::filt($_POST, $type, $len);
		}

		$value = Arr::getVal($_POST, $field);
		$value = self::filt($value, $type, $len);

		return $value;
	}
	
	public static function both($field = null, $type = "NO_FILTING", $len = -1){
		if($field == null){
			return Arr::pack(self::get(null, $type, $len), self::post(null, $type, $len));
		}

		$rs = self::get($field, $type, $len) or $rs = self::post($field, $type, $len);
		
		return $rs;
	}

	public static function input($field = null, $type = "NO_FILTING", $len = -1){
		if(!self::$input){
			parse_str(file_get_contents("php://input"), self::$input);
		}

		if(!self::$input){
			return null;
		}

		if($field === null){
			return self::$input;
		}

		$value = Arr::getVal(self::$input, $field);
		$value = self::filt($value, $type, $len);

		return $value;
	}
}
