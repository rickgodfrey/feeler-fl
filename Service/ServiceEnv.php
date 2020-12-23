<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Service;

use Feeler\Base\BaseClass;
use Feeler\Base\GlobalAccess;
use Feeler\Base\Str;

class ServiceEnv extends BaseClass {
    public static function platform():string {
        return strtolower((string)GlobalAccess::server("SERVER_SOFTWARE"));
    }

    public static function runningMode():string {
        return defined("PHP_SAPI") && Str::isAvailable(PHP_SAPI) ? PHP_SAPI : "";
    }
}