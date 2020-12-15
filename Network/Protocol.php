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
    const PROTOCOL_HTTP = "http";
    const PROTOCOL_HTTPS = "https";
    const PROTOCOL_UNKNOWN = "unknown";

    public static function isHttpProtocol():bool{
        return stripos((string)GlobalAccess::server("SERVER_PROTOCOL"), "http") === 0 ? true : false;
    }

    public static function isSecureHttpProtocol():bool{
        return Http::isSecureConn() ? true : false;
    }

    public static function name():string {
        if(self::isSecureHttpProtocol()){$protocol = self::PROTOCOL_HTTPS;}
        else if(self::isHttpProtocol()){$protocol = self::PROTOCOL_HTTP;}
        else{return "";}
        return $protocol;
    }

    public static function prefix():string{
        return ($name = self::name()) ? "{$name}://" : false;
    }
}