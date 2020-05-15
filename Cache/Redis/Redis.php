<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Cache\Redis;

use Feeler\Base\BaseClass;
use Feeler\Base\Singleton;
use Feeler\Base\Str;
use Feeler\Base\Arr;

class Redis extends Singleton {
    protected static $instances = [];
    /**
     * @var \Redis
     */
    protected static $usingInstance;

    public function __construct()
    {
        parent::__construct();
    }

    protected static function select(string $instanceName){
        if(!Str::isAvailable($instanceName)){
            return false;
        }

        if(!isset(self::$instances[$instanceName])){
            return false;
        }

        self::$usingInstance = self::$instances[$instanceName]["instance"];

        return true;
    }

    public static function init(string $instanceName, ServiceObject $serviceObject){
        if(!Str::isAvailable($instanceName)){
            return false;
        }

        self::$instances[$instanceName] = [
            "service_object" => $serviceObject,
            "instance" => (new \Redis()),
        ];

        self::select($instanceName);

        self::$usingInstance->connect($serviceObject->ipAddr, $serviceObject->port);
        if(Str::isAvailable($serviceObject->password)){
            self::$usingInstance->auth($serviceObject->password);
        }

        return true;
    }

    public static function getInstance(){
        return self::$usingInstance;
    }

    public function set(string $key, $value, $expiration = null){
        if(!Str::isAvailable($key)){
            return false;
        }

        return self::$usingInstance->set($key, $value, $expiration);
    }

    public function get(string $key){
        if(!Str::isAvailable($key)){
            return false;
        }

        return self::$usingInstance->get($key);
    }

    public function rm(string $key) :int{
        $keys = func_get_args();

        foreach($keys as $index => $key){
            if(!Str::isAvailable($key)){
                unset($keys[$index]);
                continue;
            }
        }

        $keys = Arr::tidy($keys);

        if(!Arr::isAvailable($keys)){
            return 0;
        }

        return self::$usingInstance->del($keys);
    }
}