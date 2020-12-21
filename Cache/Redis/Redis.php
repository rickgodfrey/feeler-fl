<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Cache\Redis;

use Feeler\Base\BaseClass;
use Feeler\Base\Exceptions\InvalidMethodException;
use Feeler\Base\Number;
use Feeler\Base\Str;
use Feeler\Base\Arr;
use Feeler\Base\TMultiton;

class Redis extends \Redis {
    use TMultiton;

    protected static $prefix = "";
    protected static $expiration = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function prefix(): string
    {
        return static::$prefix;
    }

    /**
     * @param mixed $prefix
     */
    public static function setPrefix($prefix): void
    {
        if(Str::isAvailable($prefix)){
            static::$prefix = $prefix;
        }
    }

    /**
     * @param $expiration
     */
    public static function setExpiration($expiration){
        if(Number::isPosiInteric($expiration)){
            static::$expiration = (int)$expiration;
        }
    }

    public function expiration(){
        return static::$expiration;
    }

    public function genKey($key) : string {
        if(!Str::isAvailable($key)){
            return "";
        }

        return $this->prefix().$key;
    }

    /**
     * @param string $instanceName
     * @param ServiceObject $serviceObject
     * @return bool
     */
    public static function init(ServiceObject $serviceObject, string $instanceName = ""){
        if($serviceObject->isPersistent == true){
            static::instance(static::instanceName($instanceName))->pconnect($serviceObject->ipAddr, $serviceObject->port);
        }
        else {
            static::instance(static::instanceName($instanceName))->connect($serviceObject->ipAddr, $serviceObject->port);
        }

        if(Str::isAvailable($serviceObject->password)){
            static::instance(static::instanceName($instanceName))->auth($serviceObject->password);
        }

        static::setPrefix($serviceObject->prefix);
        static::setExpiration($serviceObject->expiration);
    }

    /**
     * @param string $key
     * @return bool|mixed|string
     */
    public function get($key){
        if(!Str::isAvailable($key)){
            return false;
        }

        return json_decode(parent::get($this->genKey($key)), true);
    }

    public function set($key, $value, $expiration = null){
        if(!Str::isAvailable($key)){
            return false;
        }

        if($expiration === null && $this->expiration() !== null){
            $expiration = $this->expiration();
        }

        return parent::set($this->genKey($key), json_encode($value), $expiration);
    }

    public function rm($key) :int{
        $keys = func_get_args();

        foreach($keys as $index => &$key){
            if(!Str::isAvailable($key)){
                unset($keys[$index]);
                continue;
            }

            $key = $this->genKey($key);
        }
        unset($key);

        $keys = Arr::tidy($keys);

        if(!Arr::isAvailable($keys)){
            return 0;
        }

        return parent::del($keys);
    }
}