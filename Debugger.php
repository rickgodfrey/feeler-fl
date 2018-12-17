<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-3-3
 * Time: 下午10:11
 */

namespace Fl;

class Debugger{
	public static $startTime;
	public static $endTime;

	public static function start(){
		$time = microtime();
		$time = explode(" ", $time);
		$time = $time[1] + $time[0];
		self::$startTime = $time;
	}

	public static function end(){
		$time = microtime();
		$time = explode(" ", $time);
		$time = $time[1] + $time[0];
		self::$endTime = $time;
	}

	public static function executionTime($decimalPlace = 6){
		self::end();

		if(!self::$startTime || !self::$endTime || !is_int($decimalPlace) || $decimalPlace < 0){
			return false;
		}

		$time = self::$endTime - self::$startTime;

		return number_format($time, $decimalPlace);
	}
}