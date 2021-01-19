<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Utils\UUID;

use Feeler\Base\BaseClass;
use Feeler\Base\Multiton;
use Feeler\Base\Str;
use Feeler\Base\DataFormat;
use Feeler\Fl\Hardware\NetworkCard;
use Feeler\Fl\Random;
use Feeler\Fl\System\Process;
use Feeler\Fl\Time;

/**
 * Class UUID
 * @package Feeler\Fl\Utils\UUID
 * @brief Implement of RFC4122 Definition. Get more detail by visiting official web page.
 * @link https://tools.ietf.org/html/rfc4122.html
 */
class UUID extends Multiton {
    const V1 = "v1";
    const V2 = "v2";
    const V3 = "v3";
    const V4 = "v4";
    const V5 = "v5";
    const NIL = "00000000-0000-0000-0000-000000000000";
    const NAMESPACE_DNS = "6ba7b8109dad11d180b400c04fd430c8";
    const NAMESPACE_URL = "6ba7b8109dad11d180b400c04fd430c8";
    const NAMESPACE_OID = "6ba7b8109dad11d180b400c04fd430c8";
    const NAMESPACE_X500 = "6ba7b8109dad11d180b400c04fd430c8";
    const GREGORIAN_OFFSET = 122192928000000000;
    const VALID_UUID_REGEX = "^[0-9A-Fa-f]{8}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{4}-[0-9A-Fa-f]{12}$";
    const MAC_ADDR_FORMAT = '/^[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}:[0-9a-f]{2}$/i';

    protected $uuidString = "";

    public function __construct(string $uuidVersion = self::V1, string $name = "", bool $whole = false)
    {
        $this->setUUID(self::_uuid($uuidVersion, $name, $whole));
    }

    private function _uuid(string $uuidVersion, string $name = "", bool $whole = false):string{
        if(!self::defined($uuidVersion)){
            return "";
        }
        switch($uuidVersion){
            case self::V1:
            case self::V2:
            case self::V4:
                $uuid = "{".self::NAMESPACE_DNS."}";
                if($macAddr = NetworkCard::getEth0MacAddr()){$macAddr = Str::replace(":", "", $macAddr);$macAddr = str_shuffle($macAddr);$macAddr = str_split($macAddr, 2);$macAddr = Arr::join(":", $macAddr);$uuid .= "-{".md5($macAddr)."}";}
                if($uuidVersion === self::V2 && $pid = Process::pid()){$uuid .= "-{".md5($pid)."}";}
                $uuid .= "-{".md5(Random::chars(64, Random::STRING_MIXED, false))."}";
                $uuid .= "-{".Random::uniqueId()."}";
                if(Str::isAvailable($name)){$uuid .= "-{{$name}}";}
                $uuid = strtolower(substr(sha1($uuid), 0, 32));
                if($whole){$uuid = substr($uuid, 0, 8) ."-".substr($uuid, 8, 4) ."-".substr($uuid, 12, 4) ."-".substr($uuid, 16, 4) ."-".substr($uuid, 20, 12);}
                break;

            case self::V3:
            case self::V5:
                $time = self::GREGORIAN_OFFSET + (int)(Time::nowInMicro() * 10000000);
                $string = pack('NnnnH12',
                    $time & 0xffffffff,
                    $time >> 32 & 0xffff,
                    $time >> 48 & 0x0fff | 0x1000,
                    random_int(0, 0x3fff) | 0x8000,
                    trim($name)
                );
                if(strlen($string) !== 16){
                    return "";
                }
                $uuid = Str::join('-', \unpack('H8a/H4b/H4c/H4d/H12e', $string));
                break;
        }
        if(!self::isValidUUID($uuid)){
            return "";
        }

        return $uuid;
    }

    /**
     * @return mixed
     */
    public function uuidString()
    {
        return $this->uuidString;
    }

    /**
     * @param mixed $uuidString
     */
    public function setUUID(string $uuidString): void
    {
        $this->uuidString = $uuidString;
    }

    public static function isValidUUID(string $uuid):bool{
        return preg_match("/".self::VALID_UUID_REGEX."/", $uuid) ? true : false;
    }
}