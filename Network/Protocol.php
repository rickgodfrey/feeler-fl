<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network;

use Feeler\Base\GlobalAccess;
use Feeler\Fl\Network\Protocols\Http;

class Protocol {
    const PROTOCOL_HTTP = "HTTP";
    const PROTOCOL_HTTPS = "HTTPS";
    const PROTOCOL_UNKNOWN = "UNKNOWN";

    public static function isHttpProtocol():bool{
        return stripos((string)GlobalAccess::server("SERVER_PROTOCOL"), "http") === 0 ? true : false;
    }

    public static function isSecureHttpProtocol():bool{
        return Http::isSecureConn() ? true : false;
    }

    public static function name():string {
        if(self::isSecureHttpProtocol()){$protocol = self::PROTOCOL_HTTPS;}
        else if(self::isHttpProtocol()){$protocol = self::PROTOCOL_HTTP;}
        else{return false;}
        $protocol = strtolower($protocol);
        return $protocol;
    }

    public static function prefix():string{
        return ($name = self::name()) ? "{$name}://" : false;
    }
}