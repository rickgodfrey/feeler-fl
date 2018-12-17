<?php 
/**
 * Foundation Library
 * 
 * Brief: Array Constructions
 * Author: Rick Guo
 */

namespace rickguo\Fl;

class Arr{
	public function __isset($name)
	{
		return null;
	}

	//Array of keys to delete and restore to incrementing key
	public static function tidy(&$array = array()){
		if(self::isAvailable($array)){
			$array = array_values($array);

			return true;
		}
		
		return false;
	}
	
	//Sort the 2D array by the 2nd dimension value, according is the 2nd dimension key
	public static function sortByField(&$array, $field, $order = "ASC", $type = "SORT_NATURAL", $keepKey = false){
		if(!is_array($array) || !$array || !is_string($field) || trim($field) == "")
			return false;
	
		$arr1 = $arr2 = [];
		
		foreach($array as $key => $val){
			if(isset($val[$field])) {
				$arr1[$key] = $val[$field];
			}
		}
		
		if($type == "SORT_NATURAL"){
			if($order == "ASC"){
				natsort($arr1);
			}
			else if($order == "DESC"){
				natsort($arr1);
				$arr1 = array_reverse($arr1, true);
			}
			else 
				return false;
		}
		else{
			switch($type){
				case "SORT_NUMERIC":
					$type = SORT_NUMERIC;
				break;
				
				case "SORT_REGULAR":
					$type = SORT_REGULAR;
				break;
				
				case "SORT_STRING":
					$type = SORT_STRING;
				break;
				
				case "SORT_LOCALE_STRING":
					$type = SORT_LOCALE_STRING;
				break;
				
				default:
					return false;
				break;
			}
			
			if($order === "ASC")
				asort($arr1, $type);
			else if($order === "DESC")
				arsort($arr1, $type);
			else 
				return false;
		}
			
		if($keepKey){
			foreach($arr1 as $key => $val){
				$arr2[$key] = $array[$key];
			}
		}
		else{
			foreach($arr1 as $key => $val){
				$arr2[] = $array[$key];
			}
		}

		$array = $arr2;
		return true;
	}
	
	public static function sort(&$array, $order = "ASC", $type = "SORT_NATURAL", $keepKey = true){
		if(!is_array($array) || !$array)
			return false;
	
		if($type == "SORT_NATURAL"){
			if($order == "ASC"){
				natsort($array);
			}
			else if($order == "DESC"){
				natsort($array);
				$array = array_reverse($array, true);
			}
			else 
				return false;
		}
		else{
			switch($type){
				case "SORT_NUMERIC":
					$type = SORT_NUMERIC;
				break;
				
				case "SORT_REGULAR":
					$type = SORT_REGULAR;
				break;
				
				case "SORT_STRING":
					$type = SORT_STRING;
				break;
				
				case "SORT_LOCALE_STRING":
					$type = SORT_LOCALE_STRING;
				break;
				
				default:
					return false;
				break;
			}
			
			if($order === "ASC")
				asort($array, $type);
			else if($order === "DESC")
				arsort($array, $type);
			else 
				return false;
		}
		
		if(!$keepKey)
			self::tidy($array);
		
		return true;
	}
	
	public static function ksort(&$array, $order = "ASC", $type = "SORT_NATURAL"){
		if(!is_array($array) || !$array)
			return false;
	
		if($type == "SORT_NATURAL"){
			$keys = array_keys($array);
		
			if($order == "ASC"){
				natsort($keys);
			}
			else if($order == "DESC"){
				natsort($keys);
				$keys = array_reverse($keys, true);
			}
			else 
				return false;
			
			$arr1 = array();
			foreach($keys as $key){
				foreach($array as $k => $v){
					if($key === $k){
						$arr1[$key] = $v;
					}
				}
			}
			$array = $arr1;
				
			return true;
		}
		else{
			switch($type){
				case "SORT_NUMERIC":
					$type = SORT_NUMERIC;
				break;
				
				case "SORT_REGULAR":
					$type = SORT_REGULAR;
				break;
				
				case "SORT_STRING":
					$type = SORT_STRING;
				break;
				
				case "SORT_LOCALE_STRING":
					$type = SORT_LOCALE_STRING;
				break;
				
				default:
					return false;
				break;
			}
			
			if($order === "ASC")
				ksort($array, $type);
			else if($order === "DESC")
				krsort($array, $type);
			else 
				return false;
			
			return true;
		}
	}
	
	public static function slice($array, $offset, $length = null, $keepKey = true){
		return array_slice($array, $offset, $length, $keepKey);
	}
	
	public static function makeOrderField(&$array, $field, $start = 1){
		if(!self::isAvailable($array))
			return false;
		if(!$field)
			return false;
		if(!is_int($start))
			return false;
		
		foreach($array as $key => $val){
			$array[$key][$field] = $start;
			
			$start++;
		}
		
		return true;
	}
	
	//merge multi array and make the values different
	public static function merge($array){
		$params = func_get_args();
		$array = [];

		if(isset($params[1])){
			foreach($params as $param){
				if(is_array($param)){
					foreach($param as $val){
						if(is_string($val))
							$val = trim($val);
						
						if(!in_array($val, $array, true))
							$array[] = $val;
					}
				}
			}
		}
		self::tidy($array);
		
		return $array;
	}
	
	//merge multi array and make the values different
	public static function plus($array){
		$params = func_get_args();
		
		if(isset($params[1])){
			self::tidy($array);
			unset($params[0]);
			foreach($params as $param){
				if(is_array($param)){
					foreach($param as $key => $val){
						if(is_string($val))
							$val = trim($val);
						
						if(!array_key_exists($key, $array))
							$array[$key] = $val;
					}
				}
			}
		}
		
		return $array;
	}
	
	//merge multi array and make the values different
	public static function pack($array){
		$params = func_get_args();
		
		if(isset($params[1])){
			unset($params[0]);
			foreach($params as $param){
				if(is_array($param)){
					foreach($param as $key => $val){
						if(is_string($val))
							$val = trim($val);
						
						if(!empty($key) || $key === 0)
							$array[$key] = $val;
					}
				}
			}
		}
		
		return $array;
	}
	
	//merge multi array and don't check the values are unique or not
	public static function mergeAll($array){
		$params = func_get_args();
		
		if(isset($params[1])){
			self::tidy($array);
			unset($params[0]);
			foreach($params as $param){
				foreach($param as $val){
					if(is_string($val))
						$val = trim($val);
					
					$array[] = $val;
				}
			}
		}
		
		return $array;
	}
	
	public static function unique($array){
		if(is_array($array) && $array){
			$newArray = array();
		
			foreach($array as $key => $val){
				if(is_string($val))
					$val = trim($val);
				
				if(!in_array($val, $newArray))
					$newArray[$key] = $val;
			}
			
			return $newArray;
		}
		
		return $array;
	}
	
	public static function sum($array){
		$params = func_get_args();
		
		if(!self::isAvailable($array))
			$array = array();
		
		if(!isset($params[1]))
			return $array;
			
		unset($params[0]);
			
		foreach($params as $param){
			foreach($param as $key => $val){
				if(!is_numeric($val))
					continue;
			
				if(!isset($array[$key]))
					$array[$key] = $val;
				else
					$array[$key] += $val;
			}
		}
		
		return $array;
	}
	
	public static function countField($array, $field = null){
		if(!self::isAvailable($array) || $field == null)
			return 0;
		
		$params = func_get_args();
		
		$strict = true;
		if(isset($params[3]) && $params[3] == false){
			$strict = false;
		}
		
		$rs = 0;
		
		if(isset($params[2])){
			if($strict){
				foreach($array as $key => $val){
					if(isset($val[$field]) && $val[$field] === $params[2])
						$rs++;
				}
			}
			else{
				foreach($array as $key => $val){
					if(isset($val[$field]) && $val[$field] == $params[2])
						$rs++;
				}
			}
		}
		else{
			foreach($array as $key => $val){
				if(isset($val[$field]))
					$rs++;
			}
		}
		
		return $rs;
	}
	
	//For large data analysis of 2D data merge stack numbers other elements only
	public static function mergeByKey($array){
		$params = func_get_args();
		
		if(isset($params[1])){
			unset($params[0]);
			if(!$array)
				$array = array();
			
			foreach($params as $param){
				foreach($param as $key => $arr){
					if(!$arr || !is_array($arr))
						continue;

					if(!isset($array[$key]))
						$array[$key] = $arr;
					else{
						foreach($arr as $k => $v){
							if(isset($array[$key][$k])){
								if(is_numeric($array[$key][$k]) && is_numeric($v)){
									$array[$key][$k] += $v;
								}
								else if(is_array($array[$key][$k]) && is_array($v)){
									$array[$key][$k] = self::merge($array[$key][$k], $v);
								}
							}
						}
					}
				}
			}
		}
		
		return $array;
	}

	//array to object conversion
	public static function toObj($arr, $force = false){
		if($force){
			return (object)$arr;
		}

		if(gettype($arr) != "array") return $arr;
		foreach($arr as $k => $v){
			if(strripos($k, "_array") === (strlen($k) - 6)){
				$v = (array)$v;
			}
			else if(is_array($v) && is_string($k) && (strripos($k, "_object") === (strlen($k) - 7) || !preg_match("/^(?:.*(?:es|[^s]s)|(?:es|[^s]s)_[^_]*)$/i", $k))){
				$v = (object)$v;
			}

			$arr[$k] = self::toObj($v);
		}

		return $arr;
	}

	public static function utf8Encode(&$array){
		if(!self::isAvailable($array)){
			return false;
		}

		foreach($array as &$value){
			if(self::isAvailable($value)){
				self::utf8Encode($value);
			}
			else if(is_string($value)){
				$value = utf8_encode($value);
				$value = utf8_decode($value);
			}
		}
		unset($value);

		return true;
	}
	
	//checkout the values in first array, but not in the other arrays
	public static function diff($array){
		if(!is_array($array))
			return $array;
	
		$params = func_get_args();
		$diff = array();
		
		if(isset($params[1])){
			unset($params[0]);
			
			foreach($params as $arr){
				if(!is_array($arr))
					continue;
				
				foreach($array as $key => $val){
					if(!in_array($val, $arr) && !in_array($val, $diff))
						$diff[] = $val;
				}
			}
		}
		
		return $diff;
	}
	
	//checkout the same values between the first array and the other arrays
	public static function intersect($array){
		if(!is_array($array))
			return $array;
	
		$params = func_get_args();
		$same = array();
		
		if(isset($params[1])){
			unset($params[0]);
			
			foreach($params as $arr){
				if(!is_array($arr))
					continue;
				
				foreach($arr as $key => $val){
					if(in_array($val, $array) && !in_array($val, $same))
						$same[] = $val;
				}
			}
		}
		
		return $same;
	}
	
	private static function _match($regex, $data){
		preg_match($regex, $data, $matches);
		return $matches;
	}

	private static function _matchKeyWithType($data){
		return self::_match("/^\s*?\((.*?)\)\s*?(.*?)\s*$/", $data);
	}
	
	private static function _verify($data, $type){
		if(strtolower((string)gettype($data)) === $type)
			return true;
		
		return false;
	}

	public static function isLegal($arr){
		return is_array($arr) ? true : false;
	}

	public static function isAvailable($arr, $key = null, $fullVerify = true){
		if(!is_array($arr))
			return false;
		
		if(!$key)
			return $arr ? true : false;
		
		if(is_array($key)){
			foreach($key as $k){
				if($matches = self::_matchKeyWithType($k)){
					$verifiedRs = self::_verify($matches[1], $matches[0]);
					if(!$verifiedRs && $fullVerify)
						return false;
					else if($verifiedRs)
						return true;
				}
				else if(!isset($arr[$k]) && $fullVerify)
					return false;
				else if(isset($arr[$k]))
					return true;
			}
		}
		else{
			if($matches = self::_matchKeyWithType($key)){
				if(!self::_verify($matches[1], $matches[0]))
					return false;
			}
			else if(!isset($arr[$key]))
				return false;
		}
		
		return true;
	}
	
	public static function current($arr){
		if(self::isAvailable($arr))
			$arr = current($arr);
		else
			$arr = null;
		
		return $arr;
	}
	
	public static function rmVal($array, $value){
		if(self::isAvailable($array) && $value){
			foreach($array as $key => $val){
				if($val === $value){
					unset($array[$key]);
				}
			}
			
			self::tidy($array);
		}
		
		return $array;
	}

	public static function rm($array, $key){
		if(!self::isAvailable($array) || !isset($array[$key])){
			return false;
		}

		unset($array[$key]);

		return true;
	}
	
	public static function clear(&$array){
		if(self::isAvailable($array)){
			foreach($array as $key => $val){
				if(is_string($val)){
					$array[$key] = trim($val);
					if(!isset($array[$key]) || !$array[$key]){
						unset($array[$key]);
						continue;
					}
				}
				else if(!$val){
					unset($array[$key]);
					continue;
				}
			}
			
			return true;
		}
		
		return false;
	}

	public static function getVal($rs, $rsKey, $tinyMode = true, $withKey = false){
		if(empty($rsKey) || (!is_string($rsKey) && !is_int($rsKey) && !is_callable($rsKey))){
			return $rsKey;
		}

		$key = $rsKey;
		$data = null;

		if($tinyMode){
			$regex = "/^\s*(?:\(([^\(\)\:]*)(?:\:([^\(\)\:]*)?)?\))?([^\(\)]*)\s*$/i";
		}
		else{
			$regex = "/^\s*(?:\(([^\(\)\:]*)(?:\:([^\(\)\:]*)?)?\))?\{\{([^\{\}]*)\}\}\s*$/i";
		}

		if(is_callable($rsKey, false, $functionName) && strpos($functionName, "Closure::") === 0){
			$data = call_user_func($rsKey);
		}
		else if(preg_match($regex, $rsKey, $matches)) {
			$type = $matches[1];
			$type = strtolower($type);
			$defaultValue = $matches[2];
			$key = $matches[3];

			if($type == "null" || $defaultValue == "null"){
				$defaultValue = null;
			}
			else if($defaultValue === "[]"){
				$defaultValue = [];
			}
			else if($defaultValue === "{}"){
				$defaultValue = (new \stdClass());
			}
			else if($defaultValue === "\[\]"){
				$defaultValue = "[]";
			}
			else if($defaultValue === "\{\}"){
				$defaultValue = "{}";
			}

			if (isset($rs[$key]))
				$data = $rs[$key];

			if($data === null || $data === ""){
				$data = $defaultValue;
			}

			if ($type && $data !== null) {
				switch ($type) {
					case "int":
						$data = (int)$data;
						break;

					case "float":
						$data = (float)$data;
						break;

					case "bool":
						$data = (bool)$data;
						break;

					case "string":
						$data = (string)$data;
						break;

					case "array":
						$data = (array)$data;
						break;

					case "object":
						$data = (object)$data;
						break;

					default:
						break;
				}
			}
		}
		else{
			$data = $rsKey;
		}

		return $withKey ? [$key => $data] : $data;
	}

	public static function build($mappings){
		if(!self::isAvailable($mappings)){
			return [];
		}

		$vals = [];

		foreach($mappings as $newKey => $key){
			$vals[$newKey] = self::getVal(null, $key, false);
		}

		return $vals;
	}

	public static function rebuild(){
		$params = func_get_args();
		$paramsCount = func_num_args();

		if(!$params || $paramsCount < 2){
			return [];
		}

		$arrayParams = array_slice($params, 0, ($paramsCount - 1));

		$array = [];
		if(count($arrayParams) > 1){
			foreach($arrayParams as $arrayParam){
				$array = array_merge($array, $arrayParam);
			}
		}
		else{
			$array = $arrayParams[0];
		}

		$mappings = $params[$paramsCount - 1];

		if(!self::isAvailable($mappings)){
			return [];
		}

		unset($params, $arrayParams);

		$vals = [];

		foreach($mappings as $newKey => $key){
			$vals[$newKey] = self::getVal($array, $key, false);
		}

		return $vals;
	}

	public static function buildEnd(&$theArray){
		$params = func_get_args();
		$paramsCount = func_num_args();

		if(!$params){
			return false;
		}

		if($paramsCount < 2){
			return false;
		}

		$arrayParams = array_slice($params, 0, ($paramsCount - 1));
		$originalArray = $arrayParams[0];
		$array = [];
		unset($arrayParams[0]);

		if($arrayParams){
			foreach($arrayParams as $arrayParam){
				$array = array_merge($array, $arrayParam);
			}
		}

		$mappings = $params[$paramsCount - 1];

		if(!self::isAvailable($mappings)){
			return false;
		}

		unset($params, $arrayParams);

		$vals = [];

		foreach($mappings as $newKey => $key){
			$vals[$newKey] = self::getVal($array, $key, false);
		}

		$vals = array_merge($originalArray, $vals);

		$theArray = $vals;

		return true;
	}

	public static function getVals($array, $keys = []){
		if(!self::isAvailable($array) || !self::isAvailable($keys)){
			return [];
		}

		$vals = [];

		foreach($keys as $key){
			$val = self::getVal($array, $key, true, true);
			$vals[key($val)] = current($val);
		}

		return $vals;
	}

	public static function getColumn($rs = array(), $field, $unique = true){
		if(!is_array($rs) || !$rs || !$field)
			return [];

		$array = [];

		if(is_string($field) || is_int($field)){
			foreach ($rs as $val) {
				if(isset($val[$field]))
					$array[] = $val[$field];
			}

			if($unique == true && $array){
				$array = array_unique($array);
			}
		}

		return $array;
	}

	public static function isAssoc($array){
		if(!self::isAvailable($array)){
			return false;
		}

		return array_keys($array) !== range(0, count($array) - 1);
	}

	public static function trimVals(&$array){
		if(!self::isAvailable($array)){
			return false;
		}

		foreach($array as &$val){
			if(Str::isAvailable($val)){
				$val = trim($val);
			}
			else if(self::isAvailable($val)){
				self::trimVals($val);
			}
		}
		unset($val);

		return true;
	}

	public static function explode($delimiter, $string, $limit = -1){
		if(!Str::isAvailable($string)){
			return [null];
		}

		if(Number::isUnsignedInt($limit) && $limit > 1){
			$array = explode($delimiter, $string, $limit);
		}
		else{
			$array = explode($delimiter, $string);
		}

		self::trimVals($array);

		return $array;
	}

	public static function indexByKey($array, $indexKey, $columns = null, $uniqueItem = false){
		if(!Arr::isAvailable($array) || !Str::isAvailable($indexKey)){
			return [];
		}

		$rs = [];

		foreach($array as $key => $value){
			if(!isset($value[$indexKey]) || $value[$indexKey] == null){
				continue;
			}

			if(!$uniqueItem && !isset($rs[$value[$indexKey]])){
                $rs[$value[$indexKey]] = [];
            }

			if($columns === null){
			    if(!$uniqueItem){
                    $rs[$value[$indexKey]][] = $value;
                }
                else{
                    $rs[$value[$indexKey]] = $value;
                }
			}
			else if(Str::isAvailable($columns)){
                if(!$uniqueItem){
                    $rs[$value[$indexKey]][] = isset($value[$columns]) ? $value[$columns] : null;
                }
                else{
                    $rs[$value[$indexKey]] = isset($value[$columns]) ? $value[$columns] : null;
                }
			}
			else if(Arr::isAvailable($columns)){
                if(!$uniqueItem){
                    $row = [];

                    foreach($columns as $column){
                        if($column == null){
                            continue;
                        }

                        $row[$column] = isset($value[$column]) ? $value[$column] : null;
                    }

                    $rs[$value[$indexKey]][] = $row;
                }
                else{
                    if(!isset($rs[$value[$indexKey]])){
                        $rs[$value[$indexKey]] = [];
                    }

                    foreach($columns as $column){
                        if($column == null){
                            continue;
                        }

                        $rs[$value[$indexKey]][$column] = isset($value[$column]) ? $value[$column] : null;
                    }
                }
			}
		}

		return $rs;
	}

	public static function get($array, $key, $defaultValue = null){
		if(!is_array($array)){
			return $defaultValue;
		}

		if(is_null($key) || $key === "") {
			return $array;
		}

		if(array_key_exists($key, $array)) {
			return $array[$key];
		}

		foreach(explode('.', $key) as $segment){
			if(is_array($array) && array_key_exists($segment, $array)){
				$array = $array[$segment];
			}
			else{
				return $defaultValue;
			}
		}

		return $array;
	}

	public static function insert(&$array, $position, $item){
        return array_splice($array, $position, 0, $item);
    }
}
