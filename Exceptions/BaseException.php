<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Exceptions;

use Feeler\Base\Errno;

/**
 * Exception thrown if an error which can only be found on runtime occurs.
 */
class BaseException extends \Feeler\Base\Exceptions\BaseException {
    public function __construct($message = "", $code = Errno::UNSPECIFIED, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}