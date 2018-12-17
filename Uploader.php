<?php

namespace rickguo\Fl;

class Uploader{
	public $rootPath;
	public $dir;
	public $path;
	public $maxSize;
	public $force;
	public $allowTypes = "*";
	public $checkTypes = array();
	
	private $_sysErrCodeBaseNum = 1000;
	private $_commonErrCodeBaseNum = 2000;
	
	function __construct($dir, $maxSize = 20480, $force = false){
		$this->rootPath = ROOT_PATH;
		$this->dir = $dir;
		$this->path = $this->rootPath.$dir;
		$this->maxSize = $maxSize * 1024;
		$this->force = $force;
	}

	private function _getErrorCode($errCode, $sys = false){
		if(is_numeric($errCode) && $errCode){
			if($sys) $errCode += $this->_sysErrCodeBaseNum;
			else $errCode += $this->_commonErrCodeBaseNum;
		}

		return $errCode;
	}

	public function act(){
		$filesInfo = array();
		if(!is_dir($this->path)){
			File::mkdir($this->path);
		}

		foreach($_FILES as $field => $file){
			if(!isset($_FILES[$field]["name"]) || $_FILES[$field]["name"] === "")
				continue;

			if(is_array($_FILES[$field]["name"])){
				$keys = array_keys($_FILES[$field]["name"]);

				foreach($keys as $key){
					$filesInfo[$field][$key]["name"] = $_FILES[$field]["name"][$key];
					$filesInfo[$field][$key]["code"] = $this->_getErrorCode($_FILES[$field]["error"][$key], true);

					if($filesInfo[$field][$key]["code"] == 0){
						$fileExt = File::getExt($_FILES[$field]["name"][$key]);
						if($fileExt == null){
							$fileExt = File::getType($_FILES[$field]["tmp_name"][$key]);
						}

						if($this->allowTypes !== "*"){
							if(!is_array($this->allowTypes)){
								$filesInfo[$field][$key]["code"] = $this->_getErrorCode(1);
							}
							else if(is_array($fileExt) && !array_intersect($fileExt, $this->allowTypes)){
								$filesInfo[$field][$key]["code"] = $this->_getErrorCode(1);
							}
							else if(!is_array($fileExt) && !in_array($fileExt, $this->allowTypes)){
								$filesInfo[$field][$key]["code"] = $this->_getErrorCode(1);
							}
						}
						else if($_FILES[$field]["size"][$key] > $this->maxSize){
							$filesInfo[$field][$key]["code"] = $this->_getErrorCode(2);
						}

						if($filesInfo[$field][$key]["code"] == 0 && is_uploaded_file($_FILES[$field]["tmp_name"][$key])){
							if(is_array($fileExt))
								$fileExt = Arr::current($fileExt);

							$filesInfo[$field][$key]["md5"] = md5_file($_FILES[$field]["tmp_name"][$key]);
							$filesInfo[$field][$key]["name"] = $filesInfo[$field][$key]["md5"].".".$fileExt;
							$filesInfo[$field][$key]["ext"] = $fileExt;
							$filesInfo[$field][$key]["path"] = $this->path;
							$filesInfo[$field][$key]["dir"] = $this->dir;
							$filesInfo[$field][$key]["src"] = $this->dir.$filesInfo[$field][$key]["name"];
							$filesInfo[$field][$key]["file"] = $this->path.$filesInfo[$field][$key]["name"];
							$filesInfo[$field][$key]["size"] = $_FILES[$field]["size"][$key];

							$filesInfo[$field][$key]["code"] = $this->_getErrorCode(100, true);

							if((!$this->force && is_file($filesInfo[$field][$key]["file"]) && md5_file($filesInfo[$field][$key]["file"]) === $filesInfo[$field][$key]["md5"]) ||
								move_uploaded_file($_FILES[$field]["tmp_name"][$key], $filesInfo[$field][$key]["src"]))
							{
								$filesInfo[$field][$key]["code"] = 0;
							}
						}
					}
				}
			}
			else{
				$filesInfo[$field][0]["name"] = $_FILES[$field]["name"];
				$filesInfo[$field][0]["code"] = $this->_getErrorCode($_FILES[$field]["error"], true);

				if($filesInfo[$field][0]["code"] == 0){
					$fileExt = File::getExt($_FILES[$field]["name"]);
					if($fileExt == null){
						$fileExt = File::getType($_FILES[$field]["tmp_name"]);
					}

					if($this->allowTypes !== "*"){
						if(!is_array($this->allowTypes)){
							$filesInfo[$field][0]["code"] = $this->_getErrorCode(1);
						}
						else if(is_array($fileExt) && !array_intersect($fileExt, $this->allowTypes)){
							$filesInfo[$field][0]["code"] = $this->_getErrorCode(1);
						}
						else if(!is_array($fileExt) && !in_array($fileExt, $this->allowTypes)){
							$filesInfo[$field][0]["code"] = $this->_getErrorCode(1);
						}
					}
					else if($_FILES[$field]["size"] > $this->maxSize){
						$filesInfo[$field][0]["code"] = $this->_getErrorCode(2);
					}

					if($filesInfo[$field][0]["code"] == 0 && is_uploaded_file($_FILES[$field]["tmp_name"])){
						if(is_array($fileExt))
							$fileExt = Arr::current($fileExt);

						$filesInfo[$field][0]["md5"] = md5_file($_FILES[$field]["tmp_name"]);
						$filesInfo[$field][0]["name"] = $filesInfo[$field][0]["md5"].".".$fileExt;
						$filesInfo[$field][0]["ext"] = $fileExt;
						$filesInfo[$field][0]["path"] = $this->path;
						$filesInfo[$field][0]["dir"] = $this->dir;
						$filesInfo[$field][0]["src"] = $this->dir.$filesInfo[$field][0]["name"];
						$filesInfo[$field][0]["file"] = $this->path.$filesInfo[$field][0]["name"];
						$filesInfo[$field][0]["size"] = $_FILES[$field]["size"];

						$filesInfo[$field][0]["code"] = $this->_getErrorCode(100, true);

						if((!$this->force && is_file($filesInfo[$field][0]["file"]) && md5_file($filesInfo[$field][0]["file"]) === $filesInfo[$field][0]["md5"]) ||
							move_uploaded_file($_FILES[$field]["tmp_name"], $filesInfo[$field][0]["file"]))
						{
							$filesInfo[$field][0]["code"] = 0;
						}
					}
				}
			}
		}
		
		return $filesInfo;
	}
}
