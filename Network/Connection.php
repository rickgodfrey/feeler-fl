<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Network;

use Feeler\Base\Number;
use Feeler\Base\GlobalAccess;

class Connection {
    protected static function isValidPort(&$port):bool{
        if(!Number::isInteric($port)){
            return false;
        }
        $port = (int)$port;
        if($port < 0 || $port > 65535){
            return false;
        }
        return true;
    }

    public static function selfPort():int{
        $port = GlobalAccess::server("SERVER_PORT");
        return self::isValidPort($port) ? $port : false;
    }

    public static function selfIpAddr():string{
        return (string)GlobalAccess::server("SERVER_ADDR");
    }

    public static function remotePort():int{
        $port = GlobalAccess::server("REMOTE_PORT");
        return self::isValidPort($port) ? $port : false;
    }

    public static function remoteIpAddr(bool $getRealIp = true):string{
        if($getRealIp && ($ipAddr = (string)GlobalAccess::server("HTTP_CLIENT_IP")) && strcasecmp($ipAddr, IP::IP_UNKNOWN)){
            return $ipAddr;
        }
        if($getRealIp && ($ipAddr = (string)GlobalAccess::server("HTTP_X_FORWARDED_FOR")) && strcasecmp($ipAddr, IP::IP_UNKNOWN)){
            return $ipAddr;
        }
        return (string)GlobalAccess::server("REMOTE_ADDR");
    }
}