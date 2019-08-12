<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Exceptions;

class AppException extends \Exception {
    protected $type;

    function __construct($code = 0, $message = "", $type = "JSON", Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    public function renderException($e){
        if($this->type == "JSON") {
            if (!is_object($e)) {
                self::output("101", "服务器遇到了一点问题，请您稍后重试。");
            }

            self::output($e->getCode(), $e->getMessage());
        }
    }

    //输出JSON数据方法
    public static function output($code, $message, $data = []){
        if(!$code && !is_numeric($code))
            return null;

        header("Content-type: text/json");

        if($code == 0){
            if(TP_ENV == "production"){
                $code = 1;
                $message = "服务器遇到了一点问题，请您稍后重试。";
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

        $output = json_encode($output, JSON_UNESCAPED_UNICODE);

        self::responseCode(200);

        ob_end_clean();
        header("Content-Type: application/json; charset=utf-8");
        header("Content-Transfer-Encoding: binary");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");

        file_put_contents("php://output", $output);
        exit();
    }

    //返回responsecode
    protected static function responseCode($code = 200){
        if(!$code || headers_sent())
            return false;

        return http_response_code($code);
    }
}