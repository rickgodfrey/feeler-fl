<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Base\Arr;
use Feeler\Base\Multiton;
use Feeler\Base\Str;
use Feeler\Fl\Network\Protocols\Http;

class HttpSender extends Multiton
{
    protected $timeout;
    protected $headers;
    protected $basicAuth;

    public function __construct(){
        $this->setHeaders();
    }

    protected function predefinedHeaders()
    {
        return [
            "connection" => "keep-alive",
            "cache_control" => "no-cache",
            "pragma" => "no-cache",
            "user_agent" => Http::receiverInstance()->userAgent(),
            "accept" => Http::receiverInstance()->accept(),
            "referer" => "",
            "accept_encoding" => "gzip,deflate,sdch",
            "accept_language" => "en-US,zh-CN;q=0.8,zh;q=0.6",
        ];
    }

    public function setHeader(string $key, $value, bool $override = true):self{
        if(!Str::isAvailable($key)){
            return $this;
        }
        if (!Arr::isAvailable($this->headers)) {
            $this->headers = $this->predefinedHeaders();
        }
        if(!isset($this->headers[$key]) || $override){
            $this->headers[$key] = $value;
        }
        return $this;
    }

    public function setHeaders(array $headers = []):self{
        if (!Arr::isAvailable($this->headers)) {
            $this->headers = $this->predefinedHeaders();
        }

        $this->headers = Arr::mergeByKey($this->headers, $headers);

        return $this;
    }

    public function setBasicAuth($basicAuth)
    {
        if (!$basicAuth || !Str::isString($basicAuth)) {
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

        $nameArr = explode("-", $name);
        $name = "";
        foreach($nameArr as $str){
            $name .= "-".ucfirst(strtolower($str));
        }
        $name = substr($name, 1);

        return $name;
    }

    protected function convertDictToHeaderFormat(array $dict = []):array{
        if (!Arr::isAvailable($dict)) {
            return [];
        }

        $array = [];

        foreach($dict as $name => $value){
            if(!($name = $this->convertToStandardHeaderName($name))) {
                continue;
            }
            $array[] = $name.": ".trim($value);
        }

        return $array;
    }

    protected function processPostParams(&$params):void{
        if (Arr::isAvailable($params)) {
            foreach ($params as $key => &$param) {
                if (Str::isAvailable($param) && $param[0] === "@") {
                    $param = substr($param, 1);
                    if (!is_file($param)) {
                        unset($params[$key]);
                        continue;
                    }
                    $param = "@{$param}";
                }
            }
            unset($param);
        }
    }

    //packaging of the original curl api
    public function send($url = "", $method = Req::GET, $params = [])
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

        if($method === Req::GET && $params){
            if(Url::hasQueryString($url)){
                $beginSymbol = "&";
            }
            else{
                $beginSymbol = "?";
            }

            $queryString = "";
            if(Arr::isArray($params)){
                foreach($params as $key => $param){
                    $queryString .= "&".urlencode($key)."=".urlencode($param);
                }
                $queryString = substr($queryString, 1);
                $queryString = $beginSymbol.$queryString;
                $url .= $queryString;
            }
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

        if (in_array($contentType, ["application/json", "text/json", "application/xml", "text/xml"]) && Str::isString($params)) {
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
            if(in_array($method, [Req::POST, Req::PUT, Req::DELETE])){
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
        return $data;
    }
}
