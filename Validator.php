<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Base\Number;

class Validator{
    const LOCALE_COMPATIBLE = "COMPATIBLE";
    const LOCALE_CN = "CN";

	//test the legality of the mail address
	public static function testMail($mail, &$parts = null){
		$regex = "/^([a-zA-Z0-9_-]+?)@([a-zA-Z0-9-]+)((?:\.[a-zA-Z]*){1,3})$/";

		return preg_match($regex, $mail, $parts) ? true : false;
	}

	//test the legality of the phone number
	public static function testPhoneNumber($phoneNumber, $country = self::LOCALE_COMPATIBLE){
		if(!Number::isInteric($phoneNumber)){
			return false;
		}

		$regex = [
		    "COMPATIBLE" => "/^[0-9]{5,11}$/",
			"CN" => "/^1[0-9]{10}$/",
		];

		if(!isset($regex[$country])){
		    return false;
        }

		return preg_match($regex[$country], $phoneNumber) ? true : false;
	}

	//validate the string is ASCII code or not
	public static function testAscii($string){
		return mb_detect_encoding($string, ["ASCII"]) === "ASCII" ? true : false;
	}
}