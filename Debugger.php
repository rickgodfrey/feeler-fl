<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

class Debugger{
	protected static $startTime;
	protected static $endTime;
	protected static $executionTime;
	protected static $decimalPlace = 6;

	public static function startPoint(){
		$time = microtime();
		$time = explode(" ", $time);
		$time = $time[1] + $time[0];
		self::$startTime = $time;
	}

	public static function endPoint(){
		$time = microtime();
		$time = explode(" ", $time);
		$time = $time[1] + $time[0];
		self::$endTime = $time;
	}

	public static function lastExecutionTime(){
        $decimalPlace = self::$decimalPlace;

        if(!self::$startTime || !self::$endTime || !is_int($decimalPlace) || $decimalPlace < 0){
            self::$executionTime = false;
            return self::$executionTime;
        }

        $time = self::$endTime - self::$startTime;
        self::$executionTime = number_format($time, $decimalPlace);

        return self::$executionTime;
    }

    public static function setDecimalPlace($decimalPlace){
	    self::$decimalPlace = !is_int($decimalPlace) || $decimalPlace < 0 ? 6 : $decimalPlace;
    }
}