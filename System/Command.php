<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\System;

use Feeler\Base\BaseClass;
use Feeler\Base\Str;

class Command extends BaseClass {
    const UNIX_NETWORK_DETAIL = "/sbin/ifconfig -a";
    const UNIX_OS_DETAIL = "/etc/lsb_release -a";
    const WINNT_NETWORK_DETAIL = "ipconfig /all";

    protected static function detailInternal(string $needle):string{
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

    protected static function osDetailCommand():string{
        return self::detailInternal("os");
    }

    protected static function networkDetailCommand():string{
        return self::detailInternal("network");
    }

    public static function osDetail():string{
        return self::exec(self::osDetailCommand());
    }

    public static function networkDetail():string{
        return self::exec(self::networkDetailCommand());
    }
}