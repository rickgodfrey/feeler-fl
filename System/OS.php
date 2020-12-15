<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\System;

use Feeler\Base\BaseClass;
use Feeler\Base\Arr;
use Feeler\Base\Str;

class OS extends BaseClass {
    const OS_NAME = PHP_OS;

    const REGEX_DESTRIBUTION_NAME = "^Distributor\s*ID\s*:\s*(.*)$";
    const REGEX_DESTRIBUTION_VERSION = "^Release\s*:\s*([0-9\.]*)$";

    const KEY_DISTRIBUTION_NAME = "distribution_name";
    const KEY_DISTRIBUTION_VERSION = "distribution_version";

    const UNIX_MAC_ADDR_INFO_BEGIN_WITH = "ether";
    const LINUX_MAC_ADDR_INFO_BEGIN_WITH = self::UNIX_MAC_ADDR_INFO_BEGIN_WITH;
    const DARWIN_MAC_ADDR_INFO_BEGIN_WITH = self::UNIX_MAC_ADDR_INFO_BEGIN_WITH;

    protected static $detail;

    public static function name(){
        $name = strtolower(self::OS_NAME);
        if($name === "darwin"){$name = "macos";}
        return $name;
    }

    public static function family(){
        $name = self::name();
        if($name === "linux" || $name === "macos"){$family = "unix";}
        else if($name === "windows"){$family = "winnt";}
        else{$family = $name;}
        return $family;
    }

    public static function kernel(){
        $name = self::name();
        if($name === "macos"){$kernel = "darwin";}
        else if($name === "windows"){$kernel = "winnt";}
        else{$kernel = $name;}
        return $kernel;
    }

    public static function distribution() : string {
        switch(self::name()){
            case "linux":
                return self::arrayAccessStatic(self::KEY_DISTRIBUTION_NAME, "osDetail");
                break;

            case "macos":
            case "windows":
                return self::name();
                break;

            default:
                return self::UNKNOWN;
                break;
        }
    }

    public static function detail() : array {
        if(!self::$detail){
            self::$detail = [
                self::KEY_DISTRIBUTION_NAME => "",
                self::KEY_DISTRIBUTION_VERSION => "",
            ];

            if(OS::name() !== "linux"){
                return self::$detail;
            }

            $rs = Command::osDetail();
            if(Arr::isAvailable($rs)){
                foreach($rs as $info){
                    if(!Str::isAvailable($info)){
                        continue;
                    }

                    if(!Str::isAvailable(self::$detail[self::KEY_DISTRIBUTION_NAME])){
                        $matched = preg_match("/".self::REGEX_DESTRIBUTION_NAME."/i", $info, $matches);
                        if($matched){
                            self::$detail[self::KEY_DISTRIBUTION_NAME] = strtolower($matches[1]);
                            continue;
                        }
                    }
                    if(!Str::isAvailable(self::$detail[self::KEY_DISTRIBUTION_VERSION])){
                        $matched = preg_match("/".self::REGEX_DESTRIBUTION_VERSION."/i", $info, $matches);
                        if($matched){
                            self::$detail[self::KEY_DISTRIBUTION_VERSION] = strtolower($matches[1]);
                            continue;
                        }
                    }
                }
            }
        }

        return self::$detail;
    }
}