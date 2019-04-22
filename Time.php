<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

class Time{
	protected static $theDay;
	protected static $datetimeObj;
	protected static $defaultTimezone;
	protected static $timezone;

	public static function datetimeInstance(){
		if(!is_object(self::$datetimeObj)){
			self::$datetimeObj = new \DateTime();
		}

		return self::$datetimeObj;
	}

	public static function now(){
		return time();
	}

	public static function getTime($time){
		if(!Number::isInteric($time) && ($time = strtotime($time)) === false){
			return null;
		}

		return $time;
	}

	public static function getTimeInfo($timestamp = "NOW"){
		if($timestamp == "NOW"){
			return getdate();
		}

		if(Number::isInteric($timestamp)){
			return getdate($timestamp);
		}

		return null;
	}

	public static function dayTime($time){
		if(($time = self::getTime($time)) === null){
			return null;
		}

		return strtotime(self::timeToStr( $time, "Y-m-d 0:0:0"));
	}

	public static function hourTime($time){
		if(($time = self::getTime($time)) === null){
			return null;
		}

		$timeInfo = self::getTimeInfo($time);

		return strtotime(self::timeToStr( $time, "Y-m-d {$timeInfo["hours"]}:0:0"));
	}

	public static function minuteTime($time){
		if(($time = self::getTime($time)) === null){
			return null;
		}

		$timeInfo = self::getTimeInfo($time);

		return strtotime(self::timeToStr( $time, "Y-m-d {$timeInfo["hours"]}:{$timeInfo["minutes"]}:0"));
	}

	public static function dayLatestTime($time){
		if(($time = self::getTime($time)) === null){
			return null;
		}

		return strtotime(self::timeToStr( $time, "Y-m-d 23:59:59"));
	}

	public static function today(){
		return self::dayTime(time());
	}

	public static function todayLatestTime(){
		return self::dayLatestTime(time());
	}

	public static function oneDayTime(){
		return 86400;
	}

	public static function oneWeekTime(){
		return 604800;
	}

	public static function nextDay(){
		return strtotime("+1 day", self::today());
	}

	public static function yesterday(){
		return strtotime("-1 day", self::today());
	}

	public static function thisYear(){
		return strtotime(date("Y")."-1-1 0:0:0");
	}

	public static function setTheDay($theDay){
		if(($theDay = self::getTime($theDay)) === null){
			return null;
		}

		self::$theDay = self::dayTime($theDay);

		return true;
	}

	public static function resetTheDay(){
		self::$theDay = null;

		return true;
	}

	public static function theday(){
		return self::$theDay;
	}

	public static function toNextDay(){
		if(!Number::isInt(self::$theDay)){
			return null;
		}

		self::$theDay = strtotime("+1 day", self::$theDay);

		return self::$theDay;
	}

	public static function toYesterday(){
		if(!Number::isInt(self::$theDay)){
			return null;
		}

		self::$theDay = strtotime("-1 day", self::$theDay);

		return self::$theDay;
	}

	public static function timeToStr($time, $format){
		if(!Str::isAvailable($format) || !Number::isInteric($time)){
			return null;
		}

		return date($format, $time);
	}

	public static function setDefaultTimezone(){
		if(self::$defaultTimezone == null){
			self::$defaultTimezone = date_default_timezone_get();
		}

		return true;
	}

	public static function getDefaultTimezone(){
		self::setDefaultTimezone();

		return self::$defaultTimezone;
	}

	public static function setTimezone($timezone){
		self::setDefaultTimezone();

		$rs = date_default_timezone_set($timezone);
		if($rs){
			self::$timezone = $timezone;
		}

		return $rs;
	}

	public static function getTimezone(){
		if(self::$timezone == null){
			self::$timezone = self::getDefaultTimezone();
		}

		return self::$timezone;
	}

	public static function strToTime($timeStr, $timeformat = null, $time = "NOW"){
		if(!Str::isAvailable($timeStr) || !Str::isAvailable($timeformat)){
			return false;
		}

		if(self::getTimezone() != "PRC"){
			return false;
		}

		if(Number::isInteric($time)){
			self::datetimeInstance()->setTimestamp($time);
		}
		else if($time == "NOW"){
			self::datetimeInstance()->setTimestamp(time());
		}
		else{
			throw new AppException(1, "time param must be timestamp or 'NOW' flag");
		}

		if (($timeObj = self::datetimeInstance()->createFromFormat($timeformat, $timeStr)) === false) {
			return false;
		}

		return strtotime($timeObj->format("Y-m-d H:i:s"));
	}

	public static function periodDate($timestamp, $daysCount = 1){

	}
}