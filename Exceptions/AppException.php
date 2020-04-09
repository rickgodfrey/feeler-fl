<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Exceptions;

use app\versions\rootversion\models\Setting;
use Feeler\Fl\Number;

class AppException extends \Exception {
    protected $type;

    public function __construct($code = 0, $message = "", $type = "JSON", Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    /**
     * @param $e
     * @throws \Feeler\Fl\Encryption\Exception\BadFormatException
     * @throws \Feeler\Fl\Encryption\Exception\EnvironmentIsBrokenException
     * @throws \ReflectionException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function renderException($e){
        if($this->type == "JSON") {
            if (!is_object($e)) {
                self::output("1", "system error[2]");
            }

            self::output($e->getCode(), $e->getMessage());
        }
    }

    /**
     * @param $code
     * @param $message
     * @param array $data
     * @return |null
     * @throws \Feeler\Fl\Encryption\Exception\BadFormatException
     * @throws \Feeler\Fl\Encryption\Exception\EnvironmentIsBrokenException
     * @throws \ReflectionException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function output($code, $message, $data = []){
        if(!$code && !is_numeric($code))
            return null;

        header("Content-type: text/json");

        if($code == 0){
            if(TP_ENV == "production"){
                $code = 1;
                $message = "system error[1]";
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

        $output = json_encode($output, JSON_UNESCAPED_UNICODE, 1024);

        $output = Setting::instance()->encrypt($output);
        $contentLength = Number::format(strlen($output) / 1024, 2, false);

        self::responseCode(200);

        ob_end_clean();
        header("Content-Type: text/html; charset=utf-8");
        header("Content-Length: {$contentLength}");
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