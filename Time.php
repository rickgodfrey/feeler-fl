<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Base\Arr;
use Feeler\Base\Str;
use Feeler\Base\Obj;
use Feeler\Base\Number;
use Feeler\Base\TMultiton;

class Time extends \DateTime {
    use TMultiton;

    const NOW = "now";
    const US_STANDARD_TIME_FORMAT = "F d Y H:i:s";
    const US_STANDARD_DATE_FORMAT = "F d Y";

	protected static $currentTimeZone;

    public static function currentTimezone(string $zoneId = null):string{
	    if($zoneId === null){
            if(!self::$currentTimeZone){
                self::$currentTimeZone = date_default_timezone_get();
            }
        }
        else{
            (!($rs = false) && (Str::isAvailable($zoneId)) && date_default_timezone_set($zoneId)) and (self::$currentTimeZone = $zoneId) and ($rs = true);
            if($rs === false){
                throw new \Exception("Trying to set an illegal time zone");
            }
            self::$currentTimeZone = $zoneId;
        }
        return self::$currentTimeZone;
    }

	public static function timeInfo($timestamp = self::NOW):array{
	    if($timestamp === self::NOW){
            $timestamp = null;
        }
	    if($timestamp !== null && !Number::isInteric($timestamp)){
	        return [];
        }
        return getdate($timestamp);
	}

    public static function strToTime(string $timeStr, $basedTime = null, string $timeformat = null):int{
        if(!Str::isAvailable($timeStr)){
            return false;
        }
        if($basedTime === null){
            $basedTime = self::now();
        }
        if(!Number::isInteric($basedTime)){
            return false;
        }
        if(!Str::isAvailable($timeformat)){
            return strtotime($timeStr, $basedTime);
        }
        if(!($timeObj = self::createFromFormat($timeformat, $timeStr))){
            return false;
        }
        return strtotime($timeObj->format(self::US_STANDARD_TIME_FORMAT));
    }

    public static function timeToStr($time, string $format):string{
        if(!Number::isInteric($time) || !Str::isAvailable($format)){
            return false;
        }
        return date($format, $time);
    }

	public static function oneDayStartTime($time = null):int{
        return self::strToTime(self::timeToStr($time, self::US_STANDARD_DATE_FORMAT." 0:0:0"));
    }

    public static function oneDayEndTime($time = null):int{
        return self::strToTime(self::timeToStr($time, self::US_STANDARD_DATE_FORMAT." 23:59:59"));
    }

    public static function nextDayStartTime($time = null):int{
        return self::strToTime("+1 day", self::oneDayStartTime($time));
    }

    public static function nextDayEndTime($time = null):int{
        return self::strToTime("+1 day", self::oneDayEndTime($time));
    }

    public static function yesterdayStartTime($time = null):int{
        return self::strToTime("-1 day", self::oneDayStartTime($time));
    }

    public static function yesterdayEndTime($time = null):int{
        return self::strToTime("-1 day", self::oneDayEndTime($time));
    }

	public static function hourStartTime($time = null):int{
		if(!($timeInfo = self::timeInfo($time))){
		    return false;
        }
		return self::strToTime(self::timeToStr($time, self::US_STANDARD_DATE_FORMAT." {$timeInfo["hours"]}:0:0"));
	}

    public static function hourEndTime($time = null):int{
        if(!($timeInfo = self::timeInfo($time))){
            return false;
        }
        return self::strToTime(self::timeToStr($time, self::US_STANDARD_DATE_FORMAT." {$timeInfo["hours"]}:59:59"));
    }

    public static function nextHourStartTime($time = null):int{
        return self::strToTime("+1 hour", self::hourStartTime($time));
    }

    public static function nextHourEndTime($time = null):int{
        return self::strToTime("+1 hour", self::hourEndTime($time));
    }

	public static function minuteStartTime($time = null):int{
        if(!($timeInfo = self::timeInfo($time))){
            return false;
        }
        return self::strToTime(self::timeToStr($time, self::US_STANDARD_DATE_FORMAT." {$timeInfo["hours"]}:{$timeInfo["minutes"]}:0"));
	}

    public static function minuteEndTime($time = null):int{
        if(!($timeInfo = self::timeInfo($time))){
            return false;
        }
        return self::strToTime(self::timeToStr($time, self::US_STANDARD_DATE_FORMAT." {$timeInfo["hours"]}:{$timeInfo["minutes"]}:59"));
    }

    public static function nextMinuteStartTime($time = null):int{
        return self::strToTime("+1 minute", self::minuteStartTime($time));
    }

    public static function nextMinuteEndTime($time = null):int{
        return self::strToTime("+1 minute", self::minuteEndTime($time));
    }

    public static function oneWeekStartTime($time = null):int{
        if(!($timeInfo = self::timeInfo($time))){
            return false;
        }
        (($days = (int)$timeInfo["wday"]) or ($days = 7)) and ($days--);
        return self::strToTime("-{$days} day", self::oneDayStartTime($time));
    }

    public static function oneWeekEndTime($time = null):int{
        return self::strToTime("-1 second", self::strToTime("+7 day", self::oneWeekStartTime($time)));
    }

    public static function oneMonthStartTime($time = null):int{
        if(!($timeInfo = self::timeInfo($time))){
            return false;
        }
        return self::strToTime(self::timeToStr($time, "{$timeInfo["month"]} 1 {$timeInfo["year"]} 0:0:0"));
    }

    public static function oneMonthEndTime($time = null):int{
        return self::strToTime("-1 second", self::strToTime("+1 month", self::oneMonthStartTime($time)));
    }

    public static function oneYearStartTime($time = null):int{
        if(!($timeInfo = self::timeInfo($time))){
            return false;
        }
        return self::strToTime(self::timeToStr($time, "Jan 1 {$timeInfo["year"]} 0:0:0"));
    }

    public static function oneYearEndTime($time = null):int{
        if(!($timeInfo = self::timeInfo($time))){
            return false;
        }
        return self::strToTime(self::timeToStr($time, "Dec 31 {$timeInfo["year"]} 23:59:59"));
    }

    public static function minutesHaveSeconds($minutes):int{
	    if(!Number::isPosiInteric($minutes)){
	        return 0;
        }
        return $minutes * 60;
    }

    public static function hoursHaveSeconds($hours):int{
        if(!Number::isPosiInteric($hours)){
            return 0;
        }
        return $hours * 3600;
    }

    public static function daysHaveSeconds($days):int{
        if(!Number::isPosiInteric($days)){
            return 0;
        }
        return $days * 86400;
    }

    public static function weeksHaveSeconds($weeks):int{
        if(!Number::isPosiInteric($weeks)){
            return 0;
        }
        return $weeks * 604800;
    }

	public static function countPeriodDays($startTime, $endTime):int{
        if(!Number::isPosiInteric($startTime) || !Number::isPosiInteric($endTime)){
            return 0;
        }
        if(($period = $endTime - $startTime) <= 0) {
            return 0;
        }
        return ceil($period / 86400);
    }

    public static function now():int{
        return time();
    }

    public static function nowInMicro():float{
        $microtime = (string)microtime();
        if(!preg_match("/^\s*0\.[0-9]+\s*[0-9]+\s*$/", $microtime, $microtime)){
            return false;
        }
        return $microtime[2].substr($microtime[1], 1);
    }

    public static function secondInMicro():float{
        if(!($microtime = Str::join(".", self::nowInMicro(), 2))){
            return false;
        }
        return (float)("0.".$microtime[1]);
	}
}