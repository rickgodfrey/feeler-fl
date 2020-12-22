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
        return ($brief = (string)GlobalAccess::server("SERVER_PROTOCOL")) ? strtolower($brief) : "";
    }

    public static function name():string {
        return preg_match("/^([a-zA-Z-_]+)\s*\/?\s*[vV]([0-9\.]+)$/", self::brief(), $matches) ? $matches[1] : static::UNKNOWN_PROTOCOL;
    }

    public static function version():string{
        return preg_match("/^([a-zA-Z-_]+)\s*\/?\s*[vV]([0-9\.]+)$/", self::brief(), $matches) ? $matches[2] : static::UNKNOWN_PROTOCOL;
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

    public static function isHttp():bool{
        return strpos(self::scheme(), "http") === 0 ? true : false;
    }
}