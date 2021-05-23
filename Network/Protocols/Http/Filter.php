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

class HttpFilter{
    const INT = "int";
    const FLOAT = "float";
    const DOUBLE = "double";
    const STRING = "string";
    const HTML_ESCAPED = "html_escaped";
    const HTML_UNESCAPED = "html_unescaped";
    const NO_FILTERING = "no_filtering";

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
	
	//the filter act
	public static function act($data, $type = self::HTML_ESCAPED, int $len = -1){
		if(Arr::isArray($data)){
			foreach($data as $key => $val){
				$data[$key] = self::act($val);
			}
		}
		else{
			if(Str::isString($data)){
				$data = trim($data);
				$data = self::sliceStr($data, $len);
			}

			switch($type){			
                case self::INT:
					$data = (int)$data;
				break;
				
                case self::FLOAT:
					$data = (FLOAT)$data;
				break;
				
                case self::DOUBLE:
					$data = (double)$data;
				break;
				
                case self::STRING:
					$data = (string)$data;
				break;

                case self::HTML_ESCAPED:
                    if(Str::isString($data)){
                        $data = htmlspecialchars($data, ENT_QUOTES);
                    }
					break;

                case self::HTML_UNESCAPED:
					$data = self::filtHtml($data);
					break;

                case self::NO_FILTERING:
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
}
