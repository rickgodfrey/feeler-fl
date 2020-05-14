<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Cache\Redis;

use Feeler\Base\BaseClass;

class ServiceObject extends BaseClass {
    public $ipAddr;
    public $port;
    public $password;

    public function __construct($ipAddr, $port, $password = null)
    {
        parent::__construct();
    }
}