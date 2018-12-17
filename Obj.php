<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-3-28
 * Time: 下午2:31
 */

namespace Feeler\Fl;

class Obj{
	public static function toArr($obj){
		if(is_object($obj)){
			$arr = (array)$obj;
			unset($obj);
		}
		else{
			return [];
		}

		if(is_array($arr)){
			foreach($arr as $key => $val){
				$arr[$key] = self::toArr($val);
			}
		}
		return $arr;
	}

	public static function create(){
		return new \stdClass();
	}

	public static function isAvailable($obj){
		if(!is_object($obj)){
			return false;
		}

		$obj = self::toArr($obj);

		return empty($obj) ? false : true;
	}

	public static function &methodIsDefined($obj, $methodName, &$rClass = null){
	    if(!is_object($obj)){
            throw new AppException("1", "Param 1 is not a object");
        }

	    if(!is_object($rClass)){
	        $rClass = new \ReflectionClass($obj);
        }

	    if(!method_exists($rClass, "getMethod")){
            throw new AppException("1", "The param is not a reflection object");
        }

        if(!Str::isAvailable($methodName)){
            return false;
        }

        if(!method_exists($obj ,$methodName)){
	        return false;
        }

        try{
            $method = $rClass->getMethod($methodName);
        }
        catch(\ReflectionException $e){
            return false;
        }

        return $method->class == $rClass->getName() ? $rClass : false;
    }
}
