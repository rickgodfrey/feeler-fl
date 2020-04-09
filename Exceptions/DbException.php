<?php

namespace Feeler\Fl\Exceptions;

class DbException extends \Exception {
    function __construct($code = 0, $message = "", \Exception $previous = null)
    {
        \Exception::__construct($message, $code, $previous);
    }
}