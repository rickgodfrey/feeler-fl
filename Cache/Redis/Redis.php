<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Cache\Redis;

use Feeler\Base\BaseClass;
use Feeler\Base\Exceptions\InvalidMethodException;
use Feeler\Base\Str;
use Feeler\Base\Arr;

class Redis extends \Redis {
    protected static $instances = [];
    /**
     * @var \Redis
     */
    protected static $usingInstance;

    public static function __callStatic($methodName, $params)
    {
        if(method_exists(self::$usingInstance, $methodName)){
            return call_user_func_array([self::$usingInstance, $methodName], $params);
        }
        else{
            throw new InvalidMethodException("Calling invalid method: \\Redis::{$methodName}()");
        }
    }

    protected static function selectDb(string $instanceName){
        if(!Str::isAvailable($instanceName)){
            return false;
        }

        if(!isset(self::$instances[$instanceName])){
            return false;
        }

        self::$usingInstance = self::$instances[$instanceName];

        return true;
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

        self::$instances[$instanceName] = [
            "service_object" => $serviceObject,
            "instance" => (new \Redis()),
        ];

        self::selectDb($instanceName);

        self::$usingInstance->connect($serviceObject->ipAddr, $serviceObject->port);
        if(Str::isAvailable($serviceObject->password)){
            self::$usingInstance->auth($serviceObject->password);
        }
    }

    public static function getInstance(){
        return self::$usingInstance;
    }

    public static function rm(string $key) :int{
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