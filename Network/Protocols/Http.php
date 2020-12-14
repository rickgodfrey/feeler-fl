<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols;

use Feeler\Base\Arr;
use Feeler\Base\Number;
use Feeler\Base\Str;
use Feeler\Base\GlobalAccess;
use Feeler\Fl\Network\IP;
use Feeler\Fl\Network\Protocols\Exceptions\HttpException;
use Feeler\Fl\Network\Protocols\Http\HttpSender;

class Http
{
    const IP_V4 = "IP_V4";
    const IP_V6 = "IP_V6";
    const IP_INVALID = "IP_INVALID";

    public static $headers;
    public static $pathParams = [];

    public function __construct()
    {

    }

    public static function getAllHeaders(){
        if (!Arr::isAvailable(self::$headers)) {
            $headers = [];
            foreach (GlobalAccess::server() as $name => $value) {
                if (substr($name, 0, 5) == "HTTP_") {
                    $name = substr($name, 5);
                    $name = str_replace("_", " ", $name);
                    $name = str_replace(" ", "-", $name);
                    $name = strtolower($name);
                    $nameArr = explode("-", $name);
                    $name = "";
                    foreach($nameArr as $str){
                        $name .= "-".ucfirst($str);
                    }
                    $name = substr($name, 1);

                    $headers[$name] = $value;
                    $headers[strtolower(str_replace("-", "_", $name))] = $value;
                }
            }

            self::$headers = $headers;
        }

        return self::$headers;
    }

    public static function getHeader($key)
    {
        if (!Str::isAvailable($key)) {
            return null;
        }

        self::getAllHeaders();
        if (!Arr::isAvailable(self::$headers)) {
            return null;
        }

        $val = Arr::getVal(self::$headers, $key, $dataKey);

        if($dataKey == null){
            return null;
        }

        return [$dataKey => $val];
    }

    public static function getHeaders($keys)
    {
        if (!is_array($keys) || !$keys) {
            return [];
        }

        $headers = [];
        foreach ($keys as $key) {
            $rs = self::getHeader($key);
            if(($key = Arr::key($rs)) == null){
                continue;
            }
            $headers[$key] = Arr::current($rs);
        }

        return $headers;
    }

    public static function setPathParams($params)
    {
        if (!Arr::isAvailable($params)) {
            return false;
        }

        foreach ($params as $key => $value) {
            if ($value == "+") {
                $value = "";
            }

            GlobalAccess::get($key, $value);
        }

        self::$pathParams = $params;
    }

    public static function setPathParam($key, $value)
    {
        if (!Str::isAvailable($key)) {
            return false;
        }

        self::$pathParams[$key] = $value;
    }

    public static function getPathParams()
    {
        return self::$pathParams;
    }

    public static function allowRequestMethods($toCheckAllowedMethods = [])
    {
        $requestMethod = GlobalAccess::server("REQUEST_METHOD");

        if (Arr::isAvailable($toCheckAllowedMethods) && !in_array($requestMethod, $toCheckAllowedMethods)) {
            throw new HttpException("REQUEST_METHOD_ERROR", 1003);
        }
    }

    public static function requestMethod()
    {
        return (string)GlobalAccess::server("REQUEST_METHOD");
    }

    public static function isIpAddr($ipAddr)
    {
        if (!Str::isAvailable($ipAddr)) {
            return self::IP_INVALID;
        }

        $ipAddr = explode(".", $ipAddr, 4);

        $i = 1;
        foreach($ipAddr as $seg){
            if(!Number::isInteric($seg)){
                return self::IP_INVALID;
            }

            if((int)$seg > 255 || (int)$seg < 0){
                return self::IP_INVALID;
            }

            if($i > 4){
                return self::IP_INVALID;
            }

            $i++;
        }

        return self::IP_V4;
    }

    public static function ipToNumber($ipAddr) : int{
        return IP::ipToNumber($ipAddr);
    }

    /**
     * @param $ipAddrPattern
     * @return bool
     * @throws HttpException
     */
    public static function isAllowedIpAddr($ipAddrPattern)
    {
        if (!$ipAddrPattern || !is_string($ipAddrPattern)) {
            throw new HttpException("ILLEGAL_IP_ADDR", 1003);
        }

        $ipAddr = self::clientIpAddr();
        $ipVersion = self::IP_V4;
        $ipv4AddrRegex = "/^\s*?([0-9]{1,3}|\*)((?:\.(?:[0-9]{1,3}|\*)){1,3})\s*$/i";

        if (!preg_match($ipv4AddrRegex, $ipAddrPattern, $ipAddrPatternSegs)) {
            $ipVersion = self::IP_V6;
        }

        if ($ipVersion == self::IP_V4) {
            $array = explode(".", $ipAddrPatternSegs[2]);
            foreach ($array as $key => $val) {
                if ($val == "") {
                    unset($array[$key]);
                }
            }

            $ipAddrPatternSegs = Arr::mergeAll([$ipAddrPatternSegs[1]], $array);

            $ipAddrSegs = explode(".", $ipAddr);

            for ($i = 0; $i <= 3; $i++) {
                if (!isset($ipAddrPatternSegs[$i]) || !isset($ipAddrSegs[$i])) {
                    return false;
                }

                if ($ipAddrPatternSegs[$i] == "*") {
                    continue;
                }

                if ($ipAddrPatternSegs[$i] !== $ipAddrSegs[$i]) {
                    return false;
                }
            }

            return true;
        }
        else if($ipVersion == self::IP_V6){
            $ipv6Addr = "::1";

            return $ipAddr === $ipv6Addr ? true : false;
        }
        else{
            return false;
        }
    }

    /**
     * @param array $headers
     * @param string $basicAuth
     * @return HttpSender
     */
    public static function senderInstance($headers = [], $basicAuth = "")
    {
        return (new HttpSender())->constructor($headers, $basicAuth);
    }

    public static function clientIpAddr()
    {
        $ip = null;

        if (GlobalAccess::server("HTTP_X_FORWARDED_FOR")) {
            $arr = explode(",", GlobalAccess::server("HTTP_X_FORWARDED_FOR"));
            $pos = array_search("unknown", $arr);
            if ($pos !== false) {
                unset($arr[$pos]);
            }

            $ip = trim($arr[0]);
        }
        else if (GlobalAccess::server("HTTP_CLIENT_IP")) {
            $ip = GlobalAccess::server("HTTP_CLIENT_IP");
        }
        else if (GlobalAccess::server("REMOTE_ADDR")) {
            $ip = GlobalAccess::server("REMOTE_ADDR");
        }

        return $ip;
    }

    public static function isSecureConn(){
        $isSecureConn = false;
        if ((GlobalAccess::server("HTTPS") == "1" || strtolower(GlobalAccess::server("HTTPS")) === "on")) {
            $isSecureConn = true;
        }
        elseif (GlobalAccess::server("REQUEST_SCHEME") === "https") {
            $isSecureConn = true;
        }
        elseif (GlobalAccess::server("SERVER_PORT") == 443) {
            $isSecureConn = true;
        }
        elseif (GlobalAccess::server("HTTP_X_FORWARDED_PROTO") === "https") {
            $isSecureConn = true;
        }
        return $isSecureConn;
    }

    public static function responseCode($code = 200){
        if(!$code || headers_sent()){
            return false;
        }
        return http_response_code($code);
    }

    public static function userAgent():string{
        return (string)GlobalAccess::server("HTTP_USER_AGENT");
    }

    public static function accept():string{
        return (string)GlobalAccess::server("HTTP_ACCEPT");
    }

    public static function hostName(){
        return (string)GlobalAccess::server("HTTP_HOST");
    }

    public static function serverPort(){
        return (string)GlobalAccess::server("SERVER_PORT");
    }

    public static function serverName(){
        return (string)GlobalAccess::server("SERVER_NAME");
    }
}