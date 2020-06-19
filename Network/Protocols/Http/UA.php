<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Fl\Network\Protocols\Http;

class UA {
    protected static $ua;

    public static function info() {
        if(!self::$ua){
            self::$ua = Http::userAgent();
        }
        return self::$ua;
    }
}