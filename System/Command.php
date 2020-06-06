<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\System;

class Command {
    const UNIX_NETWORK_DETAIL = "ifconfig -a";
    const WINNT_NETWORK_DETAIL = "ipconfig /all";

    public static function networkDetail(){
        switch(OS::family()){
            case "unix":
                return self::UNIX_NETWORK_DETAIL;
                break;

            case "winnt":
                return self::WINNT_NETWORK_DETAIL;
                break;

            default:
                return "";
                break;
        }
    }
}