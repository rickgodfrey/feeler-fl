<?php 
/**
 * Foundation Library
 * 
 * Brief: Url Constructions
 * Author: Rick Guo
 */

namespace rickguo\Fl;

class Url{
	protected $get;
	protected $reqPath;
	protected $format = "PARAM";
	protected $params;
	
	public $excluding = [];
	
	function __construct($reqPath = null, $format = "PARAM", $params = []){
		$this->setReqPath($reqPath);
		$this->setFormat($format);
		
		if(is_array($params) && $params)
			$this->params = $params;
	}
	
	public function setReqPath($reqPath){
		if(!is_string($reqPath) || !$reqPath){
			return $this;
		}
		
		$this->reqPath = Filter::act($reqPath);
		
		return $this;
	}
	
	public function setFormat($format){
		if(!is_string($format) || !$format){
			return $this;
		}
		
		$this->format = $format;
		
		return $this;
	}
	
	public function exclude($params){
		if(!$params || !is_array($params))
			return $this;
		
		$this->excluding = $params;
		
		return $this;
	}
	
	public function gen($withSymbol = false){
		$get = $this->getParams();
		
		if($this->excluding){
			foreach($this->excluding as $key){
				if(isset($get[$key]))
					unset($get[$key]);
			}
		}
		
		$append = "";
		$symbol = "";
		$suffix = "";
		$url = "";
		
		$this->format = explode(".", $this->format, 2);
		
		if(isset($this->format[1])){
			$suffix = $this->format[1];
		}
		
		$this->format = $this->format[0];
		
		if($this->format == "PARAM"){
			foreach($get as $key => $val){
				if(is_array($val))
					continue;
				
				$key = trim($key);
				if(empty($key))
					continue;
					
				$val = trim($val);
				
				$key = Filter::act($key);
				$val = Filter::act($val);
				
				$append .= "&{$key}={$val}";
			}
			
			if($append){
				$symbol = "&";
				$append = "?".substr($append, 1);
			}
			else
				$symbol = "?";
				
			if($suffix){
				$url = "{$this->reqPath}.{$suffix}{$append}";
			}
			else{
				$url = $this->reqPath.$append;
			}
		}
		else if($this->format == "PATH"){
			foreach($get as $key => $val){
				if(is_array($val))
					continue;
				
				$key = trim($key);
				if(empty($key))
					continue;
					
				$val = trim($val);
				
				$key = Filter::act($key);
				$val = Filter::act($val);
				
				$append .= "/{$key}/{$val}";
			}
			
			if($suffix){
				$url = "{$this->reqPath}{$append}.{$suffix}";
			}
			else{
				$url = $this->reqPath.$append;
			}
		}
		else{
			return null;
		}
		
		if($withSymbol){
			$url .= $symbol;
		}
		
		return $url;
	}
	
	public function getParams(){
		if($this->get !== null)
			return $this->get;
		
		if($this->params)
		{
			$get = $this->params;
		}
		else
			$get = $_GET;
		
		return $get;
	}

	public function setParams($params = []){
		if($params){
			$get = $this->getParams();
			$get = array_merge($get, $params);
			
			$this->get = $get;
		}
		
		return $this;
	}

	public function delParams($params = []){
		if($params && is_array($params) && ($get = $this->getParams()) && is_array($get)){
			foreach($params as $k){
				$k = trim($k);
				
				if(empty($k))
					continue;
				
				if(isset($get[$k]))
					unset($get[$k]);
			}
			
			$this->get = $get;
		}
		
		return $this;
	}
	
	public function delParam($param){
		return $this->delParams([$param]);
	}
}
