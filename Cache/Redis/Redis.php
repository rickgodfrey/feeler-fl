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
use Feeler\Base\TFactory;

class Redis extends \Redis {
    use TFactory;

    const DEFAULT_INSTANCE = "default";

    protected static $prefix = "";
    protected static $expiration = null;
    /**
     * @var \Redis
     */
    protected static $usingInstance;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function prefix(): string
    {
        return self::$prefix;
    }

    /**
     * @param mixed $prefix
     */
    public static function setPrefix($prefix): void
    {
        if(Str::isAvailable($prefix)){
            self::$prefix = $prefix;
        }
    }

    /**
     * @param $expiration
     */
    public static function setExpiration($expiration){
        if(Number::isPosiInteric($expiration)){
            self::$expiration = (int)$expiration;
        }
    }

    public function expiration(){
        return self::$expiration;
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
    public static function init(string $instanceName, ServiceObject $serviceObject){
        if(!Str::isAvailable($instanceName)){
            return false;
        }

        $instance = new self();
        static::setUsingInstance($instanceName, $instance);
        static::setInstance($instanceName, $instance);

        if($serviceObject->isPersistent == true){
            static::$usingInstance->pconnect($serviceObject->ipAddr, $serviceObject->port);
        }
        else{
            static::$usingInstance->connect($serviceObject->ipAddr, $serviceObject->port);
        }

        if(Str::isAvailable($serviceObject->password)){
            static::$usingInstance->auth($serviceObject->password);
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

        return static::$usingInstance->get($this->genKey($key));
    }

    public function set($key, $value, $expiration = null){
        if(!Str::isAvailable($key)){
            return false;
        }

        if($expiration === null && $this->expiration() !== null){
            $expiration = $this->expiration();
        }

        return static::$usingInstance->set($this->genKey($key), $value, $expiration);
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

        return static::$usingInstance->del($keys);
    }
}