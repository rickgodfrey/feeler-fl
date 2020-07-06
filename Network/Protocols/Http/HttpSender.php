<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Base\BaseClass;
use Feeler\Base\Arr;
use Feeler\Base\Str;
use Feeler\Fl\Network\Protocols\Http;

class HttpSender extends BaseClass implements IHttpSender
{
    protected $timeout;
    protected $url;
    protected $params;
    protected $headers;
    protected $headersArray;
    protected $basicAuth;

    protected static function constructorName() :string {
        return self::INITIALIZE;
    }

    public function initialize($headers = [], $basicAuth = null, $timeout = 5)
    {
        $this->setHeaders($headers);
        $this->setBasicAuth($basicAuth);
        if (!is_int($timeout) || $timeout < 1) {
            $timeout = 5;
        }
        $this->timeout = $timeout;
        return $this;
    }

    protected function predefinedHeaders()
    {
        return [
            "connection" => "keep-alive",
            "cache_control" => "no-cache",
            "pragma" => "no-cache",
            "user_agent" => Http::userAgent(),
            "accept" => Http::accept(),
            "referer" => "",
            "accept_encoding" => "gzip,deflate,sdch",
            "accept_language" => "en-US,zh-CN;q=0.8,zh;q=0.6",
        ];
    }

    public function setHeaders($headers)
    {
        if (!Arr::isAvailable($headers)) {
            $this->headers = $this->predefinedHeaders();
            return $this;
        }

        foreach($headers as $name => $value){
            if(strpos($name, "-") === false){
                continue;
            }
        }

        $this->headers = Arr::mergeByKey($this->predefinedHeaders(), $headers);

        return $this;
    }

    public function setBasicAuth($basicAuth)
    {
        if (!$basicAuth || !is_string($basicAuth)) {
            $this->basicAuth = "";
            return $this;
        }

        $this->basicAuth = $basicAuth;

        return $this;
    }

    protected function convertToStandardHeaderName($name) :string{
        if(!Str::isAvailable($name)){
            return "";
        }

        $name = str_replace("_", "-", $name);
        $name = str_replace(" ", "-", $name);

        if(strpos($name, "-") !== false){
            $nameArr = explode("-", $name);
            $name = "";
            foreach($nameArr as $str){
                $name .= "-".ucfirst(strtolower($str));
            }
            $name = substr($name, 1);
        }

        return $name;
    }

    protected function convertDictToHeaderFormat($dict = []){
        if (!Arr::isAvailable($dict)) {
            return [];
        }

        $array = [];

        foreach($dict as $name => $value){
            $name = trim($name);
            if(!$name) {
                continue;
            }

            $name = $this->convertToStandardHeaderName($name);

            $array[] = $name.": ".trim($value);
        }

        return $array;
    }

    protected function processPostParams(&$params):void{
        if (Arr::isAvailable($params)) {
            foreach ($params as $key => $param) {
                if (Str::isAvailable($param) && $param[0] === "@") {
                    $param = substr($param, 1);
                    if (is_file($param)) {
                        $params[$key] = "@{$param}";
                    }
                }
            }
        }
    }

    //packaging of the original curl api
    public function send($url = "", $method = Req::GET, $params = [], callable $callback = null)
    {
        if (!Str::isAvailable($url)) {
            return false;
        }

        if(!in_array($method, [Req::GET, Req::POST, Req::PUT, Req::DELETE])){
            $method = Req::GET;
        }

        if (!Arr::isArray($params) && !Str::isAvailable($params)) {
            $params = [];
        }

        $headers = $this->headers;
        $headers = Arr::mergeByKey($headers, $this->predefinedHeaders());

        if (Str::isAvailable($this->basicAuth)) {
            $headers["authorization"] = "Basic ".base64_encode($this->basicAuth);
        }

        $contentType = null;
        foreach ($headers as $name => $value) {
            if ($name === "content_type") {
                $contentType = $value;
                break;
            }
        }

        if (in_array($contentType, ["application/json", "text/json", "application/xml", "text/xml"]) && is_string($params)) {
            $headers["content_length"] = strlen($params);
        }

        Arr::ksort($headers);
        $headers = $this->convertDictToHeaderFormat($headers);
        $method !== Req::GET and $this->processPostParams($params);

        $ch = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_ENCODING,"gzip");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if (!empty($params)) {
            if($method === Req::POST){
                curl_setopt($ch, CURLOPT_POST, true);
                if (Arr::isArray($params)) {
                    $params = http_build_query($params);
                }
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $data = curl_exec($ch);
        curl_close($ch);
        if(self::isClosure($callback)){
            $data = call_user_func($callback, $data);
        }

        return $data;
    }
}
