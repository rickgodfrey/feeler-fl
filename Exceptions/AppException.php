<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Exceptions;

abstract class AppException extends \Exception {
    protected $type;

    public function __construct($code = 0, $message = "", $type = "JSON", \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
    }

    abstract public function renderException($e);

    abstract public static function output($code, $data);
}