<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
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

    protected $prefix = "";
    protected $expiration = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function prefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix($prefix): void
    {
        if(Str::isAvailable($prefix)){
            $this->prefix = $prefix;
        }
    }

    public function setExpiration($expiration){
        if(Number::isPosiInteric($expiration)){
            $this->expiration = (int)$expiration;
        }
    }

    public function expiration(){
        return $this->expiration;
    }

    public function genKey($key) : string {
        if(!Str::isAvailable($key)){
            return "";
        }

        return $this->prefix().$key;
    }

    /**
     * @throws \Feeler\Base\Exceptions\InvalidDataDomainException
     * @throws \RedisException
     * @throws \ReflectionException
     */
    public function checkStatus():void{
        static::instance()->ping("");
    }

    /**
     * @param ServiceObject $serviceObject
     * @throws \Feeler\Base\Exceptions\InvalidDataDomainException
     * @throws \ReflectionException
     */
    public function init(ServiceObject $serviceObject){
        if($serviceObject->isPersistent == true){
            static::instance()->pconnect($serviceObject->ipAddr, $serviceObject->port);
        }
        else {
            static::instance()->connect($serviceObject->ipAddr, $serviceObject->port);
        }

        if(Str::isAvailable($serviceObject->password)){
            static::instance()->auth($serviceObject->password);
        }
        static::instance()->setPrefix($serviceObject->prefix);
        static::instance()->setExpiration($serviceObject->expiration);
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