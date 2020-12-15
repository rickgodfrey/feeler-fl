<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network;

use Feeler\Base\BaseClass;
use Feeler\Base\GlobalAccess;

class Protocol extends BaseClass {
    const UNKNOWN_PROTOCOL = "unknown";
    const PROTOCOLS_AND_SCHEMES_RELATIONS = [
        "tls" => "https",
        "ssl" => "https",
    ];

    protected static $brief;
    protected static $name;
    protected static $scheme;
    protected static $prefix;

    protected static function getProtocolsAndSchemesRelations():array{
        return static::PROTOCOLS_AND_SCHEMES_RELATIONS;
    }

    public static function brief():string{
        return (string)GlobalAccess::server("SERVER_PROTOCOL");
    }

    public static function name():string {
        return preg_match("/^([a-zA-Z-_]+)\s*\/?\s*([0-9\.]+)$/", self::brief(), $matches) ? $matches[1] : static::UNKNOWN_PROTOCOL;
    }

    public static function scheme():string{
        $scheme = ($scheme = self::arrayAccessStatic(self::name(), "getProtocolsAndSchemesRelations")) ? $scheme : self::name();
        if($scheme === static::UNKNOWN_PROTOCOL){
            $scheme = "";
        }
        return $scheme;
    }

    public static function prefix():string{
        return self::scheme() ? self::scheme() . "://" : "";
    }

    public static function serverIpAddr():string{
        return (string)GlobalAccess::server("SERVER_ADDR");
    }

    public static function remoteIpAddr():string{
        return (string)GlobalAccess::server("REMOTE_ADDR");
    }

    public static function clientIpAddr():string{
        if (GlobalAccess::server("HTTP_X_FORWARDED_FOR")) {
            $array = explode(",", GlobalAccess::server("HTTP_X_FORWARDED_FOR"));
            $index = array_search("unknown", $array);
            if ($index !== false) {unset($array[$index]);}
            if(isset($array[0])){$ipAddr = trim($array[0]);return $ipAddr;}
            else{return "";}
        }
        else if (GlobalAccess::server("HTTP_CLIENT_IP")) {
            return GlobalAccess::server("HTTP_CLIENT_IP");
        }
        else if (GlobalAccess::server("REMOTE_ADDR")) {
            return GlobalAccess::server("REMOTE_ADDR");
        }
        else{
            return "";
        }
    }

    public static function serverPort():int{
        return (int)GlobalAccess::server("SERVER_PORT");
    }

    public static function remotePort():int{
        return (int)GlobalAccess::server("REMOTE_PORT");
    }
}