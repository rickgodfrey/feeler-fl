<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Cache\Redis;

use Feeler\Base\BaseClass;
use Feeler\Base\Str;

class Redis extends BaseClass {
    protected static $instances = [];
    /**
     * @var \Redis
     */
    protected static $usingInstance;

    protected static function select(string $instanceName){
        if(!Str::isAvailable($instanceName)){
            return false;
        }

        if(!isset(self::$instances[$instanceName])){
            return false;
        }

        self::$usingInstance = self::$instances[$instanceName];

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
    }
}