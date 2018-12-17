<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-5-5
 * Time: 下午2:21
 */

namespace rickguo\Fl;

class HttpSender implements Interfaces\IHttpSender {
	protected $timeout;
	protected $url;
	protected $params;
	protected $headers;
	protected $basicAuth;

	function __construct($headers = [], $basicAuth = "", $timeout = 5)
	{
		$this->setHeaders($headers);
		$this->setBasicAuth($basicAuth);
		if(!is_int($timeout) || $timeout < 1){
			$timeout = 5;
		}

		$this->timeout = $timeout;
	}

	protected function preDefinedHeaders(){
	    return [
            "Connection: keep-alive",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "User-Agent: {$_SERVER["HTTP_USER_AGENT"]}",
            "Accept: {$_SERVER["HTTP_ACCEPT"]}",
            "Referer:",
            "Accept-Encoding: gzip,deflate,sdch",
            "Accept-Language: en-US,zh-CN;q=0.8,zh;q=0.6",
        ];
    }

	public function setHeaders($headers){
		if(!Arr::isAvailable($headers)){
			$this->headers = $this->preDefinedHeaders();
			return $this;
		}

		$this->headers = Arr::merge($this->preDefinedHeaders(), $headers);

        return $this;
	}

	public function setBasicAuth($basicAuth){
		if(!$basicAuth || !is_string($basicAuth)){
			$this->basicAuth = "";
            return $this;
		}

		$this->basicAuth = $basicAuth;

        return $this;
	}

	public function convertParamsToHeadersFormat($params = []){
        if(!Arr::isAvailable($params)){
            return [];
        }

        $headers = [];

        foreach($params as $key => $val){
            $headers[] = "{$key}: {$val}";
        }

        return $headers;
    }

	//packaging of the original curl api
	public function curlSend($url = "", $params = [], $headers = [],  $basicAuth = ""){
		if(!is_string($url) || !$url){
			return false;
		}

		if(!$params){
			$params = $this->params;
		}

		if(!$headers){
			$headers = $this->headers;
		}

		if(!$basicAuth){
			$basicAuth = $this->basicAuth;
		}

        if($basicAuth){
            $headers = Arr::merge($headers, ["Authorization: Basic ".base64_encode($basicAuth)]);
        }

        $contentType = null;
		$headersArray = [];
        foreach($headers as $header){
            $header = Arr::explode(":", $header, 2);
            if(!isset($header[1])){
                continue;
            }

            $headerKey = $header[0];
            if(!$headerKey){
                continue;
            }

            $headerValue = $header[1];
            $headerKey = strtolower($headerKey);

            if($headerKey == "content-type"){
                $contentType = $headerValue;
            }
            else if($basicAuth && $headerKey == "Authorization"){
                continue;
            }

            $headersArray[$headerKey] = $headerValue;
        }

        if(in_array($contentType, ["application/json", "text/json", "application/xml", "text/xml"]) && is_string($params)){
            $headersArray["content-length"] = strlen($params);
        }

        Arr::ksort($headersArray);

        $headers = [];
        foreach($headersArray as $headerKey => $headerValue){
            $keys = Arr::explode("-", $headerKey);

            foreach($keys as &$key){
                $key = ucfirst($key);
            }
            unset($key);

            $headerKey = implode("-", $keys);

            $headers[$headerKey] = $headerValue;
        }
        unset($headersArray);

        $headers = $this->convertParamsToHeadersFormat($headers);

		if(is_array($params)){
			foreach($params as $key => $param){
				if(is_string($param) && $param && $param[0] === "@"){
					$param = substr($param, 1);
					if(is_file($param)){
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
		@curl_setopt($ch,CURLOPT_SAFE_UPLOAD, false);
		if (!empty($params)) {
            //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POST, true);
			if(is_array($params)){
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

	public function send($url, $params = [], $headers = [], $basicAuth = "") {
		$return = '';
		if(!is_array($params)) return $return;

		$matches = parse_url($url);
		!isset($matches['host']) 	&& $matches['host'] 	= '';
		!isset($matches['path']) 	&& $matches['path'] 	= '';
		!isset($matches['query']) 	&& $matches['query'] 	= '';
		!isset($matches['port']) 	&& $matches['port'] 	= '';
		$host = $matches['host'];
		$path = $matches['path'] ? $matches['path'].($matches['query'] ? '?'.$matches['query'] : '') : '/';
		$port = !empty($matches['port']) ? $matches['port'] : 80;

		$conf_arr = array(
			'limit'		=>	0,
			'post'		=>	'',
			'cookie'	=>	'',
			'ip'		=>	'',
			'timeout'	=>	15,
			'block'		=>	TRUE,
		);

		foreach (array_merge($conf_arr, $params) as $k=>$v) ${$k} = $v;

		if($post) {
			if(is_array($post))
			{
				$post = http_build_query($post);
			}
			$out  = "POST $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			//$out .= "Referer: $boardurl\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= 'Content-Length: '.strlen($post)."\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cache-Control: no-cache\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
			$out .= $post;
		} else {
			$out  = "GET $path HTTP/1.0\r\n";
			$out .= "Accept: */*\r\n";
			//$out .= "Referer: $boardurl\r\n";
			$out .= "Accept-Language: zh-cn\r\n";
			$out .= "User-Agent: $_SERVER[HTTP_USER_AGENT]\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Cookie: $cookie\r\n\r\n";
		}
		$fp = @fsockopen(($ip ? $ip : $host), $port, $errno, $errstr, $this->timeout);
		if(!$fp) {
			return '';
		} else {
			stream_set_blocking($fp, $block);
			stream_set_timeout($fp, $this->timeout);
			@fwrite($fp, $out);
			$status = stream_get_meta_data($fp);
			if(!$status['timed_out']) {
				while (!feof($fp)) {
					if(($header = @fgets($fp)) && ($header == "\r\n" ||  $header == "\n")) {
						break;
					}
				}

				$stop = false;
				while(!feof($fp) && !$stop) {
					$data = fread($fp, ($limit == 0 || $limit > 8192 ? 8192 : $limit));
					$return .= $data;
					if($limit) {
						$limit -= strlen($data);
						$stop = $limit <= 0;
					}
				}
			}
			@fclose($fp);
			return $return;
		}
	}
}
