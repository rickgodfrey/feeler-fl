<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

class Filter{
    const INT = 1;
    const FLOAT = 2;
    const DOUBLE = 3;
    const STRING = 4;
    const HTML_ESCAPED = 5;
    const HTML_UNESCAPED = 6;
    const NO_FILTERING = 7;

	//slice the string to the length
	public static function sliceStr($text, $len = -1){
		if(is_int($len) && $len > 0){
			return mb_substr($text, 0, $len, "utf-8");
		}
		else if($len === 0) {
            return null;
        }

		return $text;
	}
	
	//the filt act
	public static function act($data, $type = "HTML_ESCAPED", $len = -1){
		if(is_array($data)){
			foreach($data as $key => $val){
				$data[$key] = self::act($val);
			}
		}
		else{
			if(is_string($data)){
				$data = trim($data);
				$data = self::sliceStr($data, $len);
			}

			switch($type){			
				case "INT":
					$data = (int)$data;
				break;
				
				case "FLOAT":
					$data = (FLOAT)$data;
				break;
				
				case "DOUBLE":
					$data = (double)$data;
				break;
				
				case "STRING":
					$data = (string)$data;
				break;

				case "HTML_ESCAPED":
					$data = htmlspecialchars((string)$data, ENT_QUOTES);
					break;

				case "HTML_UNESCAPED":
					$data = self::filtHtml($data);
					break;

				case "NO_FILTING":
				default:
				break;
			}
		}

		return $data;
	}

	protected static function filtHtml($html){
		// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed
		// this prevents some character re-spacing such as <java\0script>
		// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs
		$html = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $html);

		// straight replacements, the user should never need these since they're normal characters
		// this prevents like <IMG SRC=@avascript:alert('XSS')>
		$search = 'abcdefghijklmnopqrstuvwxyz';
		$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$search .= '1234567890!@#$%^&*()';
		$search .= '~`";:?+/={}[]-_|\'\\';
		for ($i = 0; $i < strlen($search); $i++) {
			// ;? matches the ;, which is optional
			// 0{0,7} matches any padded zeros, which are optional and go up to 8 chars

			// @ @ search for the hex values
			$html = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $html); // with a ;
			// @ @ 0{0,7} matches '0' zero to seven times
			$html = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $html); // with a ;
		}

		// now the only remaining whitespace attacks are \t, \n, and \r
		$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
		$ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');
		$ra = array_merge($ra1, $ra2);

		$found = true; // keep replacing as long as the previous round replaced something
		while ($found == true) {
			$originalHtml = $html;
			for ($i = 0; $i < sizeof($ra); $i++) {
				$pattern = '/';
				for ($j = 0; $j < strlen($ra[$i]); $j++) {
					if ($j > 0) {
						$pattern .= '(';
						$pattern .= '(&#[xX]0{0,8}([9ab]);)';
						$pattern .= '|';
						$pattern .= '|(&#0{0,8}([9|10|13]);)';
						$pattern .= ')*';
					}
					$pattern .= $ra[$i][$j];
				}
				$pattern .= '/i';
				$replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag
				$html = preg_replace($pattern, $replacement, $html); // filter out the hex tags
				if ($originalHtml == $html) {
					// no replacements were made, so exit the loop
					$found = false;
				}
			}
		}

		return $html;
	}

	//the filt act
	public static function actCallback($data, $callback){
		if(!is_callable($callback)){
			return $data;
		}

		if(is_array($data) && $data){
			foreach($data as $key => $val){
				$data[$key] = self::actCallback($val, $callback);
			}
		}
		else{
			$data = $callback($data);
		}

		return $data;
	}
}
