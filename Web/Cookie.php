<?php

namespace Feeler\Fl\Web;

use Feeler\Base\BaseClass;
use Feeler\Base\Number;
use Feeler\Base\Str;

class Cookie extends BaseClass {
    public static function set($key, $value, $expiration = 0) :bool{
        if(!Str::isAvailable($key) || $value == null){
            return false;
        }

        if(Number::isPosiInteric($expiration)){
            $expiration += time();
        }
        else if(Str::isAvailable($expiration)){
            $expiration = strtotime($expiration);
        }
        if(!Number::isPosiInteric($expiration)){
            $expiration = 0;
        }

        return setcookie($key, $value, $expiration);
    }

    public static function get($key) {
        if(!Str::isAvailable($key) || !isset($_COOKIE[$key])){
            return null;
        }

        return $_COOKIE[$key];
    }

    public static function expire($key, $expiration) : bool{
        if(!($value = self::get($key))){
            return false;
        }

        if(Number::isPosiInteric($expiration)){
            $expiration += time();
        }
        else if(Str::isAvailable($expiration)){
            $expiration = strtotime($expiration);
        }

        if(!Number::isPosiInteric($expiration)){
            return false;
        }

        return setcookie($key, $value, $expiration);
    }

    public static function rm($key) : bool{
        if(!Str::isAvailable($key) || !isset($_COOKIE[$key])){
            return true;
        }

        return setcookie($key, null);
    }
}