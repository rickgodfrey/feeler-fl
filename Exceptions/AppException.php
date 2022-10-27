<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Exceptions;

use Feeler\Base\Errno;

abstract class AppException extends BaseException {
    protected $type;

    public function __construct($message = "", $code = Errno::UNSPECIFIED, $type = "JSON", \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    abstract public function renderException($e);

    abstract public static function output($code, $message, $data);
}