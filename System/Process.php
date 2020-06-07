<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\System;

class Process {
    public static function pid() :?int {
        return !empty(($pid = posix_getpid())) ? $pid : null;
    }
}