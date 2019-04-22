<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

class Exception extends \Exception {
	function __construct($code = 0, $message = "", \Exception $previous = null)
	{
		\Exception::__construct($message, $code, $previous);
	}
}