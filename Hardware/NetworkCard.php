<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Hardware;

use Feeler\Base\Str;
use Feeler\Base\Arr;
use Feeler\Fl\System\OS;
use Feeler\Fl\System\Command;

class NetworkCard {
    protected static $netCardsInfo = [];
    protected static $netCardsInfoProcessed = [];

    public static function getCardPrefix() : string {
        switch(OS::name()){
            case "linux":
                return "eth";
            case "macos":
                return "en";
            default:
                return "";
        }
    }

    public static function macAddrInfoBeginWith() : string {
        switch(OS::name()){
            case "linux":
                return OS::LINUX_MAC_ADDR_BEGIN_WITH;
            case "macos":
                return OS::DARWIN_MAC_ADDR_BEGIN_WITH;
            default:
                return "";
        }
    }

    public static function getNetCardsInfo() : array {
        if(!self::$netCardsInfo){
            if(($cardName = self::getCardPrefix())){
                $rs = Command::networkDetail();
                $rs = Str::splitToArrayByDelimiter($rs, PHP_EOL);
                if(!$rs){
                    return [];
                }
                $firstRowMatched = false;
                $endFlagCrossedRows = 0;
                $cardInfo = [];
                foreach($rs as $info){
                    if(!Str::isAvailable($info)){
                        continue;
                    }
                    if(!$firstRowMatched && preg_match("/{$cardName}(?<card_number>(?:[0-9]{1})|(?:[1-9][0-9]+))\s*(?:\:)?/i", $info, $matches)){
                        $firstRowMatched = true;
                    }
                    if($firstRowMatched && preg_match("/status\s*(?:\:)?/i", $info)){
                        $endFlagCrossedRows++;
                    }
                    if($firstRowMatched && $endFlagCrossedRows <= 1){
                        $cardInfo[] = $info;
                    }
                }
                if($firstRowMatched){
                    $cardNumber = (int)$matches["card_number"];
                    self::$netCardsInfo[$cardNumber] = $cardInfo;
                }
            }
        }
        return self::$netCardsInfo;
    }

    public static function getNetCardInfo(int $cardNumber = 0):array{
        self::getNetCardsInfo();
        return isset(self::$netCardsInfo[$cardNumber]) ? self::$netCardsInfo[$cardNumber] : [];
    }

    public static function getNetCardId(int $cardNumber = 0) : string {
        if(!($cardInfo = self::getNetCardInfo($cardNumber))){
            return "";
        }

        $beginWith = self::macAddrInfoBeginWith();
        $cardIdMatched = false;
        foreach($cardInfo as $info){
            if(Str::isAvailable($info) && preg_match("/{$beginWith}\s*((?:[0-9a-z]{2})(?:[:0-9a-z]{3}){5})/i", $info, $matches)){
                if(!isset(self::$netCardsInfoProcessed[$cardNumber])){
                    self::$netCardsInfoProcessed[$cardNumber] = [];
                }
                self::$netCardsInfoProcessed[$cardNumber]["card_id"] = $matches[1];
                $cardIdMatched = true;
                break;
            }
        }
        return $cardIdMatched ? self::$netCardsInfoProcessed[$cardNumber]["card_id"] : "";
    }
}