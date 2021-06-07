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
use Feeler\Fl\Network\Protocols\Http\Exceptions\HttpException;

class IP {
    const IP_V4 = "IPv4";
    const IP_V6 = "IPv6";
    const IP_INVALID = "IP_INVALID";
    const IP_UNKNOWN = "unknown";
    const IP_V4_REGEX = "\s*([0-9]{1,3}|\*)((?:\.(?:[0-9]{1,3}|\*)){1,3})\s*";
    const IP_V6_REGEX = "\s*([0-9A-Fa-f]{1,4})?(?:(?:\:)|(?:[0-9A-Fa-f]{1,4})|\*)*(\:(?:(?:[0-9A-Fa-f]{1,4})|\*))\s*";

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
        return (Number::isInteric($number) ? (string)long2ip($number) : "") ?: "";
    }

    public static function isAllowedIpRange(string $ipAddr1, string $ipAddr2, string $remoteIpAddr = ""):bool{
        $ip1Version = self::version($ipAddr1);
        if($ip1Version === self::IP_INVALID){
            return false;
        }
        $ip2Version = self::version($ipAddr2);
        if($ip2Version === self::IP_INVALID){
            return false;
        }
        if($ip1Version !== $ip2Version){
            return false;
        }
        $remoteIpAddr = Str::isAvailable($remoteIpAddr) ? $remoteIpAddr : Connection::remoteIpAddr();
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

    public function isAllowedIpAddr(string $ipAddrPattern, string $remoteIpAddr = "")
    {
        if (!Str::isAvailable($ipAddrPattern)) {
            return false;
        }

        $ipv4AddrRegex = "/^".self::IP_V4_REGEX."$/i";
        $ipv6AddrRegex = "/^".self::IP_V6_REGEX."$/i";
        if (preg_match($ipv4AddrRegex, $ipAddrPattern, $ipAddrPatternSegs)) {
            $patternIpVersion = self::IP_V4;
        }
        else if(preg_match($ipv6AddrRegex, $ipAddrPattern, $ipAddrPatternSegs)) {
            if(substr_count($ipv6AddrRegex, "::") > 1 || substr_count($ipv6AddrRegex, ":::") > 0){
                return false;
            }
            $patternIpVersion = self::IP_V6;
        }
        else{
            return false;
        }

        $remoteIpAddr = Str::isAvailable($remoteIpAddr) ? $remoteIpAddr : Connection::remoteIpAddr();
        $remoteIpVersion = self::version($remoteIpAddr);
        if($remoteIpVersion === self::IP_INVALID || $patternIpVersion !== $remoteIpAddr){
            return false;
        }

        $ipAddrStart = str_replace("*", "0", $ipAddrPattern);
        if ($remoteIpVersion === self::IP_V6) {
            $ipAddrEnd = str_replace("*", "ffff", $ipAddrPattern);
        }
        else {
            $ipAddrEnd = str_replace("*", "255", $ipAddrPattern);
        }

        return self::isAllowedIpRange($ipAddrStart, $ipAddrEnd, $remoteIpAddr);
    }
}