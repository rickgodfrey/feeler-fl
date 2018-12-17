<?php 

namespace Fl;

defined("HEAD") or define("HEAD", 1);
defined("END") or define("END", 2);

class File {
	public $segLength = 524288; //to read and write slice in segments, this set every segment's length
	
	protected $whatAmI;
	protected $state = true;
	protected $position = 0;
	protected $handle;
	protected $fileSize;
	protected $file;

	function __construct($file, $mode = "r", $pointer = null, $override = false){
		$this->init($file, $mode, $pointer, $override);
	}
	
	function __destruct(){
		if(is_resource($this->handle))
			fclose($this->handle);
	}
	
	public function init($file, $mode = "r", $pointer = null, $override = false){
		if(is_file($file)){
			$this->whatAmI = 1;
		}
		else if(@get_headers($file, 1)){
			$this->whatAmI = 2;
		}
		else if($file && strpos($mode, "w") !== false){
			$this->whatAmI = 3;
		}
		else{
			$this->state = false;
			return false;
		}

		$this->file = $file;
		
		$lockMode = LOCK_SH;
		
		if($mode == "r"){
			$modeParam = "r";
		}
		else if($mode == "w"){
			if(!$pointer)
				$pointer = END;
			
			if($override)
				$modeParam = "w";
			else{
				if($pointer == HEAD){
					$modeParam = "c";
				}
				else if($pointer == END){
					$modeParam = "a";
				}
			}
		}
		else if($mode == "rw"){
			if(!$pointer)
				$pointer = HEAD;
			
			if($override)
				$modeParam = "w+";
			else{
				if($pointer == HEAD){
					$modeParam = "r+";
				}
				else if($pointer == END){
					$modeParam = "a+";
				}
			}
		}
		else{
			$this->state = false;
			return false;
		}
		
		if($modeParam != "r")
			$lockMode = LOCK_EX;
		
		$this->handle = fopen($file, $modeParam);
		
		if($this->whatAmI == 1){
			if($this->lock($lockMode))
				$this->fileSize = filesize($this->file);
			else{
				$this->state = false;
				return false;
			}
		}
		else if($this->whatAmI == 2){
			$this->fileSize = "NOT_AVAILABLE";
		}
	}
	
	public function getData($length = -1, $position = null){
		if($position === null)
			$position = 0;
	
		if(!$this->state || !is_int($position) || $position < 0)
			return false;
		
		if($length === -1)
			$length = $this->fileSize;
		
		$originalPosition = $this->position;
		$this->seek($position);
		$data = null;
		
		while($dataSize = (strlen($data)) < $length){
			if(($remain = $length - $dataSize) < $this->segLength)
				$data .= fread($this->handle, $remain);
			else
				$data .= fread($this->handle, $this->segLength);
		}
		
		$this->seek($originalPosition);
		
		return $data;
	}
	
	public function getDataCallback($callback, $position = null, $length = -1){
		if($position === null)
			$position = 0;
		
		if(!$this->state || !is_int($position) || $position < 0 || !is_callable($callback))
			return false;
		
		if($length === -1)
			$length = $this->fileSize;
		
		$originalPosition = $this->position;
		$this->seek($position);
		
		if($this->fileSize === "NOT_AVAILABLE"){
			while($data = fread($this->handle, $this->segLength)){
				$callback($data);
			}
		}
		else{
			$dataSize = 0;
			while($dataSize < $length){
				if(($remain = $length - $dataSize) < $this->segLength){
					$callback(fread($this->handle, $remain));
					$dataSize += $this->segLength;
				}
				else{
					$callback(fread($this->handle, $this->segLength));
					$dataSize += $this->segLength;
				}
			}
		}
		
		$this->seek($originalPosition);
		
		return true;
	}
	
	public function seek($position = 0){
		if($position === null)
			$position = 0;
	
		if(!is_int($position) || $position < 0)
			return false;
	
		if(@fseek($this->handle, $position) !== 0){
			return false;
		}
		
		$this->position = $position;
		
		return true;
	}
	
	public function write($data, $length = -1){
		if(!$this->state || !$data)
			return false;
		
		if($length === -1)
			return fwrite($this->handle, $data);
		
		if($length > 0)
			return fwrite($this->handle, $data, $length);
		
		return false;
	}
	
	public function lock($mode = LOCK_EX){
		return flock($this->handle, $mode);
	}
	
	public function unlock(){
		return flock($this->handle, LOCK_UN);
	}
	
	public static function create($file){
		return fopen($file, "w") !== false ? true : false;
	}
	
	public static function saveAs($file, $content, $length = -1){
		$fileObj = new self($file, "w", null, true);
		$rs = $fileObj->write($content, $length);

		return is_file($file) ? true : false;
	}

	//make new dirs, will create all unexist dirs on the path
	public static function mkdir($path, $chmod = 0755){
		return is_dir($path) || mkdir($path, $chmod, true);
	}
	
	public static function rm($target, $recursive = false){
		if(file_exists($target)){
			if($recursive){
				if(is_dir($target)){
					$handle = opendir($target);
					
					while($subTarget = readdir($handle)){
						if($subTarget !== "." && $subTarget !== ".."){
							$position = "{$target}/{$subTarget}";
							
							if(is_dir($position)){
								self::rm($position);
							}
							else{
								unlink($position);
							}
						}
					}
					
					closedir($handle);
					
					if(rmdir($target)){
						return true;
					}
				}
			}
			
			if(is_file($target)){
				return unlink($target);
			}
		}
		
		return false;
	}
	
	public static function rmdir($dir){
		return self::rm($dir, true);
	}
	
	//read the first and last 512byte data and convert to hex then check it whether have trojans signature code or not
	public static function checkHex($file){
		$handle = fopen($file, "rb");
		$fileSize = filesize($file);
		fseek($handle, 0);
		
		if($fileSize > 512){
			$hexCode = bin2hex(fread($handle, 512));
			fseek($handle, $fileSize - 512);
			$hexCode .= bin2hex(fread($handle, 512));
		}
		else{
			$hexCode = bin2hex(fread($handle, $fileSize));
		}
		fclose($handle);
		/**
		 * match <% (  ) %> 
		 * 		 <? (  ) ?> 
		 * 		 <script  /script> 
		 */
		if(preg_match("/(3c25.*?28.*?29.*?253e)|(3c3f.*?28.*?29.*?3f3e)|(3C534352495054.*?2F5343524950543E)|(3C736372697074.*?2F7363726970743E)/is", $hexCode))
			return false;
		else
			return true;
	}

	public static function getPathInfo($file){
	    return pathinfo($file);
    }

	//get the extension of the file
	public static function getExt($fileName){
		if(is_file($fileName)){
			$fileInfo = pathinfo($fileName);
			return isset($fileInfo["extension"]) ? $fileInfo["extension"] : null;
		}
		else{
			return strtolower(substr(strrchr($fileName, "."), 1));
		}
	}
	
	public static function getName($fileName){
        if((!$pathInfo = pathinfo($fileName))){
            return null;
        }

        return $pathInfo["filename"];
	}
	
	public static function getFullName($file){
        if((!$pathInfo = pathinfo($file))){
            return null;
        }

        return $pathInfo["basename"];
	}
	
	public static function getPath($file){
        if((!$pathInfo = pathinfo($file))){
            return null;
        }

        return $pathInfo["dirname"];
	}
	
	//get file size
	public static function getSize($file){
		return is_file($file) ? filesize($file) : null;
	}
	
    public static function getTypeList(){
    	return [
	    	"255216" => "jpeg",
			"13780" => "png",
			"7173" => "gif",
			"6677" => "bmp",
			"6063" => "xml",
			"60104" => "html",
			"208207" => array("xls", "doc"),
			"8075" => array("zip", "docx", "xlsx"),
			"8297" => "rar",
    	];
    }
	
	//get file's type according to start of 2bytes binary data
	public static function getType($file){
		if(!is_file($file)){
			return null;
		}
		
		$handle = fopen($file, "rb");
	    if(!$handle){
			return null;
		}
        $bin = fread($handle, 2);
        fclose($handle);
		
        if($bin){
        	$strs = unpack("C2str", $bin);
        	
	        $typeCode = $strs["str1"].$strs["str2"];
			$types = self::getTypeList();
			
			foreach($types as $key => $type){
				if((string)$key === $typeCode){
					return $type;
				}
			}
        }
        
		return null;
	}

	//get file's type according to start of 2bytes binary data
	public static function getTypeByContent($content){
		if(!is_string($content) || !$content){
			return null;
		}

		$bin = substr($content, 0, 2);

		if($bin){
			$strs = unpack("C2str", $bin);

			$typeCode = $strs["str1"].$strs["str2"];
			$types = self::getTypeList();

			foreach($types as $key => $type){
				if((string)$key === $typeCode){
					return $type;
				}
			}
		}

		return null;
	}
}
