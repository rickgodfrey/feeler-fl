<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Base\Arr;
use Feeler\Base\GlobalAccess;

class Req{
    const GET = "get";
    const POST = "post";
    const PUT = "put";
    const DELETE = "delete";

    protected static $input;

    public static function filter($data, $type = Filter::HTML_ESCAPED, $len = -1){
        return Filter::act($data, $type, $len);
    }

    public static function get($field = null, $type = Filter::HTML_ESCAPED, $len = -1){
        if($field === null){
            return self::filter(GlobalAccess::get(), $type, $len);
        }

        $value = Arr::getVal(GlobalAccess::get(), $field);
        $value = self::filter($value, $type, $len);

        return $value;
    }

    public static function post($field = null, $type = Filter::HTML_ESCAPED, $len = -1){
        if($field === null){
            return self::filter(GlobalAccess::post(), $type, $len);
        }

        $value = Arr::getVal(GlobalAccess::post(), $field);
        $value = self::filter($value, $type, $len);

        return $value;
    }

    public static function both($field = null, $type = Filter::HTML_ESCAPED, $len = -1){
        if($field == null){
            return Arr::mergeByKey(self::get(null, $type, $len), self::post(null, $type, $len));
        }

        $rs = self::get($field, $type, $len) or $rs = self::post($field, $type, $len);

        return $rs;
    }

    public static function input($field = null, $type = Filter::HTML_ESCAPED, $len = -1){
        if(!self::$input){
            parse_str(file_get_contents("php://input"), self::$input);
        }

        if(!self::$input){
            return null;
        }

        if($field === null){
            return self::$input;
        }

        $value = Arr::getVal(self::$input, $field);
        $value = self::filter($value, $type, $len);

        return $value;
    }
}
