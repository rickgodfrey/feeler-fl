<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Network\Protocols\Http\Exceptions;

use Feeler\Base\Obj;
use Feeler\Base\Errno;
use Feeler\Fl\Exceptions\BaseException;

class HttpException extends BaseException {
    public function __construct($message = "", $code = Errno::UNSPECIFIED, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}