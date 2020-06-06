<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Fl\Hardware\NetworkCard;

class Random{
    const UUID_ZONE_FLAG = "7eb4014b7da8e2ffcbaec069a5b6c87c";

	public static function uuid(bool $whole = false): string {
        $uuid = self::UUID_ZONE_FLAG;
	    if($macAddr = NetworkCard::getEth0MacAddr()){$uuid .= "::".md5($macAddr);}
        $uuid .= "::".md5(self::string(92, false, false));
        $uuid .= "::".md5(uniqid(mt_rand((int)((double)Time::microSecond() * 100000000), (int)9223372036854775807), true));
        $uuid = strtolower(substr(sha1($uuid), 0, 32));
		if($whole){$uuid = substr($uuid, 0, 8) ."-".substr($uuid, 8, 4) ."-".substr($uuid, 12, 4) ."-".substr($uuid, 16, 4) ."-".substr($uuid, 20, 12);}
		return $uuid;
	}

    /**
     * @param $length
     * @param bool $isNumeric
     * @param bool $withUUID
     * @return string
     */
    public static function string($length, bool $isNumeric = false, bool $withUUID = true) :string {
        $hash = "";
        if($withUUID){$seed = ($seed = Random::uuid()).strtoupper($seed);}
        else{$seed = str_shuffle("ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz`1234567890-=~!@#$%^&*()_+[];',?/{}|:<>.");}
        $seed = base_convert($seed, 16, ($isNumeric ? 10 : 36));
        $max = strlen($seed) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }

    /**
     * @param $length
     * @return string
     */
    public static function number($length) : string {
        return self::string($length, true);
    }
}
