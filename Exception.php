<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-3-3
 * Time: 上午11:06
 */

namespace rickguo\Fl;

class Exception extends \Exception {
	function __construct($code = 0, $message = "", \Exception $previous = null)
	{
		\Exception::__construct($message, $code, $previous);
	}
}