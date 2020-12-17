<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network;

use Feeler\Base\Number;

class Connection {
    protected static function checkPort(&$port):bool{
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
        return self::checkPort($port) ? $port : false;
    }

    public static function selfIpAddr():string{
        return (string)GlobalAccess::server("SERVER_ADDR");
    }

    public static function remotePort():int{
        $port = GlobalAccess::server("REMOTE_PORT");
        return self::checkPort($port) ? $port : false;
    }

    public static function remoteIpAddr(bool $getRealIp = true):string{
        if($getRealIp && ($ipAddr = (string)GlobalAccess::server("HTTP_CLIENT_IP")) && strcasecmp($ipAddr, "unknown")){
            return $ipAddr;
        }
        if($getRealIp && ($ipAddr = (string)GlobalAccess::server("HTTP_X_FORWARDED_FOR")) && strcasecmp($ipAddr, "unknown")){
            return $ipAddr;
        }
        return (string)GlobalAccess::server("REMOTE_ADDR");
    }
}