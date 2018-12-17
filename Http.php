<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-3-3
 * Time: 下午10:11
 */

namespace Feeler\Fl;

class Http
{
    public static $headers;
    public static $pathParams = [];

    function __construct()
    {

    }

    public static function getAllHeaders()
    {
        if (!Arr::isAvailable(self::$headers)) {
            $headers = [];

            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == "HTTP_") {
                    $headers[strtolower(str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5))))))] = $value;
                }
            }

            self::$headers = $headers;
        }

        return self::$headers;
    }

    public static function getHeader($key)
    {
        if (!$key || !is_string($key)) {
            return null;
        }

        if (!is_array(self::$headers) || !self::$headers) {
            self::$headers = self::getAllHeaders();
        }

        if (!self::$headers || !isset(self::$headers[$key])) {
            return null;
        }

        return isset(self::$headers[$key]) ? self::$headers[$key] : null;
    }

    public static function getHeaders($keys)
    {
        if (!is_array($keys) || !$keys) {
            return [];
        }

        $headers = [];
        foreach ($keys as $key) {
            $headers[$key] = self::getHeader($key);
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

            $_GET[$key] = $value;
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
        $requestMethod = $_SERVER["REQUEST_METHOD"];

        if (Arr::isAvailable($toCheckAllowedMethods) && !in_array($requestMethod, $toCheckAllowedMethods)) {
            throw new HttpException(1003, "REQUEST_METHOD_ERROR");
        }
    }

    public static function requestMethod()
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    public static function isAllowedIpAddr($ipAddrPattern, $ipVersion = "V4")
    {
        if (!$ipAddrPattern || !is_string($ipAddrPattern)) {
            throw new HttpException(1003, "ILLEGAL_IP_ADDR");
        }

        $ipAddr = $_SERVER["REMOTE_ADDR"];

        if ($ipVersion == "V4") {
            $ipv4AddrRegex = "/^\s*?([0-9]{1,3}|\*)((?:\.(?:[0-9]{1,3}|\*)){1,3})\s*$/i";
            $ipv6AddrRegex = "";

            if (!preg_match($ipv4AddrRegex, $ipAddrPattern, $ipAddrPatternSegs)) {
                return false;
            }

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
        } else {
            return false;
        }
    }

    public static function getSenderInstance($headers = [], $basicAuth = "")
    {
        return new HttpSender($headers, $basicAuth);
    }

    public static function clientIpAddr()
    {
        $ip = null;
        if ($ip !== null) {
            return $ip;
        }

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if ($pos !== false) {
                unset($arr[$pos]);
            }

            $ip = trim($arr[0]);
        }
        else if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }
}