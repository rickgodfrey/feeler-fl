<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Fl\Arr;
use Feeler\Fl\Str;

class HttpSender implements IHttpSender
{
    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";
    const DELETE = "DELETE";

    protected $timeout;
    protected $url;
    protected $params;
    protected $headers;
    protected $headersArray;
    protected $basicAuth;

    function __construct($headers = [], $basicAuth = null, $timeout = 5)
    {
        $this->setHeaders($headers);
        $this->setBasicAuth($basicAuth);
        if (!is_int($timeout) || $timeout < 1) {
            $timeout = 5;
        }

        $this->timeout = $timeout;
    }

    protected function preDefinedHeaders()
    {
        return [
            "connection" => "keep-alive",
            "cache_control" => "no-cache",
            "pragma" => "no-cache",
            "user_agent" => isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : "",
            "accept" => isset($_SERVER["HTTP_ACCEPT"]) ? $_SERVER["HTTP_ACCEPT"] : "",
            "referer" => "",
            "accept_encoding" => "gzip,deflate,sdch",
            "accept_language" => "en-US,zh-CN;q=0.8,zh;q=0.6",
        ];
    }

    public function setHeaders($headers)
    {
        if (!Arr::isAvailable($headers)) {
            $this->headers = $this->preDefinedHeaders();
            return $this;
        }

        foreach($headers as $name => $value){
            if(strpos($name, "-") === false){
                continue;
            }
        }

        $this->headers = Arr::mergeByKey($this->preDefinedHeaders(), $headers);

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

    public function convertToStandardHeaderName($name) :string{
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

    public function convertDictToHeaderFormat($dict = []){
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

    //packaging of the original curl api
    public function curlSend($url = "", $params = [], $headers = [], $basicAuth = "")
    {
        if (!is_string($url) || !$url) {
            return false;
        }

        if (!$params) {
            $params = $this->params;
        }

        if (!$headers) {
            $headers = $this->headers;
        }
        else{
            $headers = Arr::mergeByKey($headers, $this->preDefinedHeaders());
        }

        if (!$basicAuth) {
            $basicAuth = $this->basicAuth;
        }

        if ($basicAuth) {
            $headers["authorization"] = "Basic ".base64_encode($basicAuth);
        }

        $contentType = null;
        $headersArray = [];
        foreach ($headers as $name => $value) {
            if ($name == "content_type") {
                $contentType = $value;
                break;
            }
        }

        if (in_array($contentType, ["application/json", "text/json", "application/xml", "text/xml"]) && is_string($params)) {
            $headers["content_length"] = strlen($params);
        }

        Arr::ksort($headers);

        $headers = $this->convertDictToHeaderFormat($headers);

        if (is_array($params)) {
            foreach ($params as $key => $param) {
                if (is_string($param) && $param && $param[0] === "@") {
                    $param = substr($param, 1);
                    if (is_file($param)) {
                        $params[$key] = "@{$param}";
                    }
                }
            }
        }

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

        //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");

        if (!empty($params)) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (is_array($params)) {
                $params = http_build_query($params);
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public function send($url = "", $params = [], $headers = [], $basicAuth = ""){
        return $this->curlSend($url, $params, $headers, $basicAuth);
    }
}
