<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Utils\Test;

use Feeler\Base\Number;
use Feeler\Fl\Time;

class Debug{
    protected static $startTime;
    protected static $endTime;
    protected static $executionTime;
    protected static $decimalPlace = 8;

    public static function startPoint(){
        self::$startTime = Time::nowInMicro();
    }

    public static function endPoint(){
        self::$endTime = Time::nowInMicro();
    }

    public static function lastExecutionTime(){
        $decimalPlace = self::$decimalPlace;

        if(!self::$startTime || !self::$endTime || !is_int($decimalPlace) || $decimalPlace < 0){
            self::$executionTime = false;
            return self::$executionTime;
        }

        $time = self::$endTime - self::$startTime;
        self::$executionTime = Number::decimalFormat($time, $decimalPlace, false);

        return self::$executionTime;
    }

    public static function setDecimalPlace(int $decimalPlace){
        self::$decimalPlace = $decimalPlace < 0 ? 8 : $decimalPlace;
    }
}