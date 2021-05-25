<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network;

use Feeler\Base\Number;
use Feeler\Base\Str;
use Feeler\Base\ByteFormat;

class IP {
    const IP_V4 = "IPv4";
    const IP_V6 = "IPv6";
    const IP_INVALID = "IP_INVALID";
    const IP_UNKNOWN = "unknown";

    public static function version(string $ipAddr):string{
        if(self::isValidIPv6($ipAddr)){
            return self::IP_V6;
        }
        if(self::isValidIPv4($ipAddr)){
            return self::IP_V4;
        }
        return self::IP_INVALID;
    }

    public static function isValidIP($ipAddr) : bool{
        return Str::isAvailable($ipAddr) && filter_var($ipAddr, FILTER_VALIDATE_IP) ? true : false;
    }

    public static function isValidIPv4($ipAddr) : bool{
        return Str::isAvailable($ipAddr) && filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? true : false;
    }

    public static function isValidIPv6($ipAddr) : bool{
        return Str::isAvailable($ipAddr) && filter_var($ipAddr, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ? true : false;
    }

    public static function convertIpToNumber(string $ipAddr) : string{
        return Str::isAvailable($ipAddr) ? ByteFormat::convertBinaryToString(decbin(ip2long($ipAddr))) : false;
    }

    public static function convertNumberToIp($number) : string{
        return (Number::isInteric($number) ? ($rs = long2ip($number)) : "") ?: "";
    }

    public static function isAllowedIpRange($ipAddr1, $ipAddr2):bool{
        $ip1Version = self::version($ipAddr1);
        if($ip1Version === self::IP_INVALID){
            return false;
        }
        $ip2Version = self::version($ipAddr2);
        if($ip2Version === self::IP_INVALID){
            return false;
        }
        $remoteIpAddr = Connection::remoteIpAddr();
        $remoteIpVersion = self::version($remoteIpAddr);
        if($remoteIpVersion === self::IP_INVALID){
            return false;
        }
        if($ip1Version !== $ip2Version || $ip1Version !== $remoteIpVersion){
            return false;
        }
        if($ip1Version === self::IP_V6){
            $ip1Bin = inet_pton($ipAddr1);
            $ip2Bin = inet_pton($ipAddr2);
            $remoteIpAddrBin = inet_pton($remoteIpAddr);
        }
        else{
            $ip1Bin = self::convertIpToNumber($ipAddr1);
            $ip2Bin = self::convertIpToNumber($ipAddr2);
            $remoteIpAddrBin = self::convertIpToNumber($remoteIpAddr);
        }
        if ((strlen($remoteIpAddrBin) !== strlen($ip1Bin)) || $remoteIpAddrBin < $ip1Bin || $remoteIpAddrBin > $ip2Bin) {
            return false;
        }
        return true;
    }

    public static function isAllowedIpAddr($ipAddr):bool{
        return self::isAllowedIpRange($ipAddr, $ipAddr);
    }
}