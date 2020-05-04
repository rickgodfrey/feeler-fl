<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Fl\Network\Protocols\Http;
use Feeler\Fl\Number;

class HttpException extends \Exception{
	protected $type;

    public function __construct($code = 0, $message = "", $type = "JSON", \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    public function renderException($e){
        if($this->type == "JSON") {
            if (!is_object($e)) {
                self::output("1", "An error has occurred.");
            }

            self::output($e->getCode(), $e->getMessage());
        }
    }

	public static function output($code, $message, $data = [], callable $outputCallback = null){
		if(!$code && !is_numeric($code)){
		    exit();
        }

		if($code == 0){
			if(defined(TP_ENV) && TP_ENV == "production"){
				$code = 1;
				$message = "An error has occurred.";
			}
			else{
				$code = 1;
			}
		}

		$output = [
			"code" => (string)$code,
			"msg" => (string)$message,
		];

		if($data){
			$output["data"] = $data;
		}

        $output = json_encode($output, JSON_UNESCAPED_UNICODE, 512);
		call_user_func($outputCallback, $output);
        $contentLength = strlen($output);

        Http::responseCode(200);
        ob_end_clean();
        header("Content-Type: application/json; charset=utf-8");
        header("Content-Length: {$contentLength}");
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-cache, post-check=0, pre-check=0");
        header("Pragma: no-cache");

        file_put_contents("php://output", $output);
        exit();
	}
}