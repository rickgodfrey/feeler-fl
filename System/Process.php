<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\System;

class Process {
    public static function pid() :?int {
        return !empty(($pid = posix_getpid())) ? $pid : null;
    }
}