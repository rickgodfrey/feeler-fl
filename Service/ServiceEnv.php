<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
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
        return defined("PHP_SAPI") && ($runningMode = PHP_SAPI) && Str::isAvailable($runningMode) ? $runningMode : "";
    }
}