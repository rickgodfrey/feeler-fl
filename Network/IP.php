<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network;

class IP {
    const IP_V4 = "IP_V4";
    const IP_V6 = "IP_V6";
    const IP_INVALID = "IP_INVALID";

    public static function isIpAddr($ipAddr)
    {
        if (!Str::isAvailable($ipAddr)) {
            return self::IP_INVALID;
        }

        $ipAddr = explode(".", $ipAddr, 4);

        $i = 1;
        foreach($ipAddr as $seg){
            if(!Number::isInteric($seg)){
                return self::IP_INVALID;
            }

            if((int)$seg > 255 || (int)$seg < 0){
                return self::IP_INVALID;
            }

            if($i > 4){
                return self::IP_INVALID;
            }

            $i++;
        }

        return self::IP_V4;
    }

    public static function isIpV4($ipAddr) : bool{
        return (self::isIpAddr($ipAddr) === self::IP_V4) ? true : false;
    }
}