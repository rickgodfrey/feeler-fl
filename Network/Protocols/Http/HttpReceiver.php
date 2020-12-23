<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Base\Arr;
use Feeler\Base\Number;
use Feeler\Base\Str;
use Feeler\Base\GlobalAccess;
use Feeler\Base\Singleton;
use Feeler\Fl\Network\Connection;
use Feeler\Fl\Network\IP_Checker;
use Feeler\Fl\Network\Protocol;
use Feeler\Fl\Network\Protocols\Http\Exceptions\HttpException;
use Feeler\Fl\Service\ServiceEnv;

class HttpReceiver extends Singleton
{
    protected $headers;
    protected $pathParams = [];

    public function getAllHeaders(){
        if (!Arr::isAvailable($this->headers)) {
            $headers = [];
            if(ServiceEnv::platform() === "apache"){
                foreach (GlobalAccess::server() as $name => $value) {
                    if (substr($name, 0, 5) === "HTTP_") {
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
                    else if($name === "REDIRECT_STATUS"){
                        $key = "STATUS";
                        $headers[$key] = $value;
                        GlobalAccess::server($key, $value);
                    }
                    else if($name === "REDIRECT_HTTP_AUTHORIZATION"){
                        $key = "HTTP_AUTHORIZATION";
                        $authMethod = explode(" ", $value);
                        if(!isset($authMethod[1])){
                            continue;
                        }
                        $authType = $authMethod[0];
                        $authInfo = $authMethod[1];
                        if(!Str::isAvailable($authType) || !Str::isAvailable($authInfo)){
                            continue;
                        }
                        $authType = strtolower($authType);
                        if($authType !== "basic"){
                            continue;
                        }
                        $authInfo = base64_decode($authInfo);
                        if(!Str::isAvailable($authInfo)){
                            continue;
                        }
                        $authInfo = explode(":", $authInfo);
                        if(!isset($authInfo[1])){
                            continue;
                        }
                        $authUsername = $authInfo[0];
                        $authPassword = $authInfo[1];
                        if(!Str::isAvailable($authUsername) || !Str::isAvailable($authPassword)){
                            continue;
                        }

                        $headers[$key] = $value;
                        $headers["PHP_AUTH_USER"] = $authUsername;
                        $headers["PHP_AUTH_PW"] = $authPassword;
                        GlobalAccess::server($key, $value);
                        GlobalAccess::server("PHP_AUTH_USER", $authUsername);
                        GlobalAccess::server("PHP_AUTH_PW", $authPassword);
                    }
                }
            }
            else{
                foreach (GlobalAccess::server() as $name => $value) {
                    if (substr($name, 0, 5) === "HTTP_") {
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
            }

            $this->headers = $headers;
        }

        return $this->headers;
    }

    public function getHeader($key)
    {
        if (!Str::isAvailable($key)) {
            return null;
        }

        $this->getAllHeaders();
        if (!Arr::isAvailable($this->headers)) {
            return null;
        }

        $val = Arr::getVal($this->headers, $key, $dataKey);

        if($dataKey == null){
            return null;
        }

        return [$dataKey => $val];
    }

    public function getHeaders($keys)
    {
        if (!Arr::isArray($keys) || !$keys) {
            return [];
        }

        $headers = [];
        foreach ($keys as $key) {
            $rs = $this->getHeader($key);
            if(($key = Arr::key($rs)) == null){
                continue;
            }
            $headers[$key] = Arr::current($rs);
        }

        return $headers;
    }

    public function setPathParams($params)
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

        $this->pathParams = $params;
    }

    public function setPathParam($key, $value)
    {
        if (!Str::isAvailable($key)) {
            return false;
        }

        $this->pathParams[$key] = $value;
    }

    public function getPathParams()
    {
        return $this->pathParams;
    }

    public function setBasicAuth(string $username = "", string $password = "", string $msg = "") :void{
        $setted = 0;
        if(Str::isAvailable($username)){
            $setted += 01;
        }
        if(Str::isAvailable($password)){
            $setted += 02;
        }

        if(Str::isAvailable($msg)){
            $setted += 04;
        }
        else{
            $msg = "";
        }

        if($setted === 0){
            return;
        }
        if($setted < 03) {
            throw new HttpException("Illegal setting of basic auth");
        }

        $inputUsername = GlobalAccess::server("PHP_AUTH_USER");
        $inputPassword = GlobalAccess::server("PHP_AUTH_PW");

        if($inputUsername !== $username || $inputPassword !== $password)
        {
            header("WWW-Authenticate: Basic realm=\"{$msg}\"");
            header("HTTP/".self::version()." 401 Unauthorized");
            exit();
        }
    }

    public function allowRequestMethods($toCheckAllowedMethods = [])
    {
        $requestMethod = $this->requestMethod();

        if (Arr::isAvailable($toCheckAllowedMethods) && !in_array($requestMethod, $toCheckAllowedMethods)) {
            throw new HttpException("Request method error");
        }
    }

    /**
     * @param $ipAddrPattern
     * @return bool
     * @throws HttpException
     */
    public function isAllowedIpAddr($ipAddrPattern)
    {
        if (!$ipAddrPattern || !Str::isString($ipAddrPattern)) {
            throw new HttpException("ILLEGAL_IP_ADDR", 1003);
        }

        $ipAddr = $this->clientIpAddr();
        $ipVersion = IP_Checker::IP_V4;
        $ipv4AddrRegex = "/^\s*?([0-9]{1,3}|\*)((?:\.(?:[0-9]{1,3}|\*)){1,3})\s*$/i";

        if (!preg_match($ipv4AddrRegex, $ipAddrPattern, $ipAddrPatternSegs)) {
            $ipVersion = IP_Checker::IP_V6;
        }

        if ($ipVersion == IP_Checker::IP_V4) {
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
        else if($ipVersion == IP_Checker::IP_V6){
            $ipv6Addr = "::1";
            return $ipAddr === $ipv6Addr ? true : false;
        }
        else{
            return false;
        }
    }

    public function version(){
        return Protocol::version();
    }

    public function isSecureConn(){
        return Protocol::scheme() === "https" ? true : false;
    }

    public function responseCode($code = 200){
        if(!$code || headers_sent()){
            return false;
        }
        return http_response_code($code);
    }

    public function userAgent():string{
        return (string)GlobalAccess::server("HTTP_USER_AGENT");
    }

    public function accept():string{
        return (string)GlobalAccess::server("HTTP_ACCEPT");
    }

    public function hostName():string{
        return Protocol::isHttp() ? (string)GlobalAccess::server("SERVER_NAME") : false;
    }

    public function hostAddr():string{
        return Protocol::isHttp() ? Connection::selfIpAddr() : false;
    }

    public function hostPort():int{
        return Protocol::isHttp() ? Connection::selfPort() : false;
    }

    public function requestMethod():string{
        return (string)GlobalAccess::server("REQUEST_METHOD");
    }

    public function requestHost():string{
        return Protocol::isHttp() ? $this->hostName().$this->hostPort() : false;
    }

    public function clientIpAddr():string{
        return Connection::remoteIpAddr();
    }
}
