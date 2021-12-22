<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Base\Arr;
use Feeler\Base\Str;
use Feeler\Base\File;
use Feeler\Base\Number;
use Feeler\Base\GlobalAccess;

class Uploader{
	public $rootPath;
	public $dir;
	public $path;
	public $maxSize;
	public $force;
	public $allowTypes = "*";
	public $checkTypes = [];
	
	private $_sysErrCodeBaseNum = 1000;
	private $_commonErrCodeBaseNum = 2000;
	
	public function __construct($dir, $maxSize = 20480, $force = false){
		$this->dir = $dir;
		$this->path = $this->rootPath.$dir;
		$this->maxSize = $maxSize * 1024;
		$this->force = $force;
	}

    /**
     * @return mixed
     */
    public function getRootPath()
    {
        return $this->rootPath;
    }

    /**
     * @param mixed $rootPath
     */
    public function setRootPath($rootPath): void
    {
        $this->rootPath = $rootPath;
    }

	private function _getErrorCode($errCode, $sys = false){
		if(Number::isNumeric($errCode) && $errCode){
			if($sys) $errCode += $this->_sysErrCodeBaseNum;
			else $errCode += $this->_commonErrCodeBaseNum;
		}

		return $errCode;
	}

	private function _getFilesInfo(){
		$filesInfo = [];
		if(!is_dir($this->path)){
			File::mkdir($this->path);
		}

		foreach(GlobalAccess::files() as $field => $file){
			if(!isset($file["name"]) || !Str::isAvailable($file["name"]))
				continue;

			if(Arr::isArray($file["name"])){
				$keys = array_keys($file["name"]);

				foreach($keys as $key){
					$filesInfo[$field][$key]["name"] = $file["name"][$key];
					$filesInfo[$field][$key]["code"] = $this->_getErrorCode($file["error"][$key], true);

					if($filesInfo[$field][$key]["code"] == 0){
						$fileExt = File::getExt($file["name"][$key]);
						if($fileExt == null){
							$fileExt = File::getType($file["tmp_name"][$key]);
						}

						if($this->allowTypes !== "*"){
							if(!Arr::isArray($this->allowTypes)){
								$filesInfo[$field][$key]["code"] = $this->_getErrorCode(1);
							}
							else if(Arr::isArray($fileExt) && !array_intersect($fileExt, $this->allowTypes)){
								$filesInfo[$field][$key]["code"] = $this->_getErrorCode(1);
							}
							else if(!Arr::isArray($fileExt) && !in_array($fileExt, $this->allowTypes)){
								$filesInfo[$field][$key]["code"] = $this->_getErrorCode(1);
							}
						}
						else if($file["size"][$key] > $this->maxSize){
							$filesInfo[$field][$key]["code"] = $this->_getErrorCode(2);
						}

						if($filesInfo[$field][$key]["code"] == 0 && is_uploaded_file($file["tmp_name"][$key])){
							if(Arr::isArray($fileExt))
								$fileExt = Arr::current($fileExt);

							$filesInfo[$field][$key]["md5"] = md5_file($file["tmp_name"][$key]);
							$filesInfo[$field][$key]["name"] = $filesInfo[$field][$key]["md5"].".".$fileExt;
							$filesInfo[$field][$key]["ext"] = $fileExt;
							$filesInfo[$field][$key]["path"] = $this->path;
							$filesInfo[$field][$key]["dir"] = $this->dir;
							$filesInfo[$field][$key]["src"] = $this->dir.$filesInfo[$field][$key]["name"];
							$filesInfo[$field][$key]["file"] = $this->path.$filesInfo[$field][$key]["name"];
							$filesInfo[$field][$key]["size"] = $file["size"][$key];

							$filesInfo[$field][$key]["code"] = $this->_getErrorCode(100, true);

							if((!$this->force && is_file($filesInfo[$field][$key]["file"]) && md5_file($filesInfo[$field][$key]["file"]) === $filesInfo[$field][$key]["md5"]) ||
								move_uploaded_file($file["tmp_name"][$key], $filesInfo[$field][$key]["src"]))
							{
								$filesInfo[$field][$key]["code"] = 0;
							}
						}
					}
				}
			}
			else{
				$filesInfo[$field][0]["name"] = $file["name"];
				$filesInfo[$field][0]["code"] = $this->_getErrorCode($file["error"], true);

				if($filesInfo[$field][0]["code"] == 0){
					$fileExt = File::getExt($file["name"]);
					if($fileExt == null){
						$fileExt = File::getType($file["tmp_name"]);
					}

					if($this->allowTypes !== "*"){
						if(!Arr::isArray($this->allowTypes)){
							$filesInfo[$field][0]["code"] = $this->_getErrorCode(1);
						}
						else if(Arr::isArray($fileExt) && !array_intersect($fileExt, $this->allowTypes)){
							$filesInfo[$field][0]["code"] = $this->_getErrorCode(1);
						}
						else if(!Arr::isArray($fileExt) && !in_array($fileExt, $this->allowTypes)){
							$filesInfo[$field][0]["code"] = $this->_getErrorCode(1);
						}
					}
					else if($file["size"] > $this->maxSize){
						$filesInfo[$field][0]["code"] = $this->_getErrorCode(2);
					}

					if($filesInfo[$field][0]["code"] == 0 && is_uploaded_file($file["tmp_name"])){
						if(Arr::isArray($fileExt))
							$fileExt = Arr::current($fileExt);

						$filesInfo[$field][0]["md5"] = md5_file($file["tmp_name"]);
						$filesInfo[$field][0]["name"] = $filesInfo[$field][0]["md5"].".".$fileExt;
						$filesInfo[$field][0]["ext"] = $fileExt;
						$filesInfo[$field][0]["path"] = $this->path;
						$filesInfo[$field][0]["dir"] = $this->dir;
						$filesInfo[$field][0]["src"] = $this->dir.$filesInfo[$field][0]["name"];
						$filesInfo[$field][0]["file"] = $this->path.$filesInfo[$field][0]["name"];
						$filesInfo[$field][0]["size"] = $file["size"];
						$filesInfo[$field][0]["code"] = $this->_getErrorCode(100, true);

						if((!$this->force && is_file($filesInfo[$field][0]["file"]) && md5_file($filesInfo[$field][0]["file"]) === $filesInfo[$field][0]["md5"]) ||
							move_uploaded_file($file["tmp_name"], $filesInfo[$field][0]["file"]))
						{
							$filesInfo[$field][0]["code"] = 0;
						}
					}
				}
			}
		}
		return $filesInfo;
	}

	public function act():array{
        $filesInfo = $this->_getFilesInfo();
        $filesObjs = [];
        foreach($filesInfo as $field => $fieldFilesInfo){
            foreach($filesInfo as $fileNumber => $fieldFileInfo) {
                $file = new File($fieldFileInfo["file"]);
                $file->setFileSrc($fieldFileInfo["src"]);
                $filesObjs[$field][$fileNumber] = $file;
            }
        }
        return $filesObjs;
    }
}
