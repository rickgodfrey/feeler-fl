<?php

namespace Feeler\Fl\Utils\Lock;

use Feeler\Fl\Cache\Redis\Redis;

class ResLock
{
    private $lockSymbol = 'cxxxx:%s';

    protected static $redisInstance;


    public function __construct(Redis $redisInstance)
    {
        self::$redisInstance = $redisInstance;
    }

    private function getKey($resource_unique_name)
    {
        return str_replace('%s', $resource_unique_name, $this->lockSymbol);
    }

    /**
     * @param $resSymbol
     * @return bool
     */
    public function chechState($resSymbol)
    {
        $lockSymbol = $this->getKey($resSymbol);
        $locked = self::$redisInstance->get($lockSymbol);
        if ($locked) {
            return false;
        }
        return true;
    }

    /**
     * @param $resource_unique_name
     * @param $lock_second
     * @return void
     * @throws \RedisException
     */
    public function set($resource_unique_name, $lock_second = 300)
    {
        $lock_key = $this->getKey($resource_unique_name);
        self::$redisInstance->setex($lock_key, $lock_second, "1");
    }

    /**
     * @brief Release ResLock
     * @param $resource_unique_name
     * @return int|\Redis
     * @throws \RedisException
     */
    public function release($resource_unique_name)
    {
        $lockKey = $this->getKey($resource_unique_name);
        return self::$redisInstance->del($lockKey);
    }
}