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
use Feeler\Base\TFactory;

class Redis extends \Redis {
    const DEFAULT_INSTANCE = "default";

    use TFactory;

    /**
     * @var \Redis
     */
    protected static $usingInstance;

    public function __construct()
    {
        parent::__construct();
    }

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

        if(!isset(static::$instances[$instanceName])){
            return false;
        }

        static::$usingInstance = static::$instances[$instanceName];

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

        static::$instances[$instanceName] = new self();

        static::selectDb($instanceName);

        static::$usingInstance->connect($serviceObject->ipAddr, $serviceObject->port);

        if(Str::isAvailable($serviceObject->password)){
            static::$usingInstance->auth($serviceObject->password);
        }
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

        return static::$usingInstance->del($keys);
    }
}