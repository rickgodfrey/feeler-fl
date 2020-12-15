<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\System;

use Feeler\Base\BaseClass;
use Feeler\Base\Str;

class Command extends BaseClass {
    const UNIX_NETWORK_DETAIL = "ifconfig -a";
    const UNIX_OS_DETAIL = "lsb_release -a";
    const WINNT_NETWORK_DETAIL = "ipconfig /all";

    protected static function detailInternal(string $needle){
        $osFamily = OS::family();
        $osFamily = strtoupper($osFamily);
        $needle = strtoupper($needle);

        return (string)self::constValue("{$osFamily}_{$needle}_DETAIL");
    }

    public static function exec(string $command):string{
        if(!Str::isAvailable($command)){
            return "";
        }

        return (string)@shell_exec($command);
    }

    protected static function osDetailCommand(){
        return self::detailInternal("os");
    }

    protected static function networkDetailCommand(){
        return self::detailInternal("network");
    }

    public static function osDetail(){
        return self::exec(self::osDetailCommand());
    }

    public static function networkDetail(){
        return self::exec(self::networkDetailCommand());
    }
}