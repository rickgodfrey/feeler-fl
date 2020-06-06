<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Hardware;

use Feeler\Base\Str;
use Feeler\Base\Arr;
use Feeler\Fl\System\OS;
use Feeler\Fl\System\Command;

class NetworkCard {
    protected static $eth0Info = [];

    public static function getCardPrefix() : string {
        switch(OS::name()){
            case "linux":
                return "eth";
                break;

            case "macos":
                return "en";
                break;

            default:
                return "";
                break;
        }
    }

    public static function macAddrInfoBeginWith() : string {
        switch(OS::name()){
            case "linux":
                return OS::LINUX_MAC_ADDR_INFO_BEGIN_WITH;
                break;

            case "macos":
                return OS::DARWIN_MAC_ADDR_INFO_BEGIN_WITH;
                break;

            default:
                return "";
                break;
        }
    }

    public static function getEth0Info() : array {
        if(!self::$eth0Info){
            if(($cardName = self::getCardPrefix())){
                $cardName .= "0";
                @exec(Command::networkDetail(), $rs);
                $firstRowMatched = false;
                $endFlagCrossedRows = 0;
                $cardInfo = [];
                foreach($rs as $info){
                    if(!Str::isAvailable($info)){
                        continue;
                    }

                    if(!$firstRowMatched && preg_match("/{$cardName}\s*(?:\:)?/i", $info)){
                        $firstRowMatched = true;
                    }
                    if($firstRowMatched && preg_match("/status\s*(?:\:)?/i", $info)){
                        $endFlagCrossedRows++;
                    }
                    if($firstRowMatched && $endFlagCrossedRows <= 1){
                        $cardInfo[] = $info;
                    }
                }
                self::$eth0Info = $cardInfo;
            }
        }

        return self::$eth0Info;
    }

    public static function getEth0MacAddr() : string {
        if(!($rs = self::getEth0Info())){return "";}
        $macAddr = "";
        $beginWith = self::macAddrInfoBeginWith();
        foreach($rs as $info){
            if(Str::isAvailable($info) && preg_match("/{$beginWith}\s*((?:[0-9a-z]{2})(?:[:0-9a-z]{3}){5})/i", $info, $matches)){
                $macAddr = $matches[1];
                break;
            }
        }
        return $macAddr;
    }
}