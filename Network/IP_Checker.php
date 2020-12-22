<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network;

use Feeler\Base\Number;
use Feeler\Base\Str;

class IP_Checker {
    const IP_V4 = "IPv4";
    const IP_V6 = "IPv6";
    const IP_INVALID = "IP_invalid";

    public static function version($ipAddr){
        if(self::isIPv6($ipAddr)){
            return self::IP_V6;
        }
        if(self::isIPv4($ipAddr)){
            return self::IP_V4;
        }
        return self::IP_INVALID;
    }

    public static function isValidIp($ipAddr) : bool{
        return Str::isAvailable($ipAddr) && filter_var($ipAddr, FILTER_VALIDATE_IP) ? true : false;
    }

    public static function isIPv4($ipAddr) : bool{
        return Str::isAvailable($ipAddr) && filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? true : false;
    }

    public static function isIPv6($ipAddr) : bool{
        return Str::isAvailable($ipAddr) && filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_NO_RES_RANGE) ? true : false;
    }

    public static function ipToNumber($ipAddr) : int{
        return Str::isAvailable($ipAddr) ? ip2long($ipAddr) : false;
    }

    public static function numberToIp($number) : string{
        return (Number::isInteric($number) ? ($rs = long2ip($number)) : "") ?: "";
    }
}