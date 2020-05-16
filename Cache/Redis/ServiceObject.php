<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Cache\Redis;

use Feeler\Base\BaseClass;
use Feeler\Base\Number;
use Feeler\Base\Str;

class ServiceObject extends BaseClass {
    public $ipAddr;
    public $port;
    public $password;
    public $prefix;
    public $isPersistent = false;
    public $expiration = null;

    /**
     * @param mixed $prefix
     */
    public function setPrefix(string $prefix): void
    {
        if(Str::isAvailable($prefix)){
            $this->prefix = $prefix;
        }
    }

    /**
     * @param bool $isPersistent
     */
    public function isPersistent(bool $isPersistent): void
    {
        $this->isPersistent = $isPersistent;
    }

    /**
     * @param null $expiration
     */
    public function setExpiration($expiration): void
    {
        if(Number::isPosiInteric($expiration)){
            $this->expiration = (int)$expiration;
        }
    }

    public function __construct($ipAddr, $port, $password = null)
    {
        parent::__construct();
        $this->ipAddr = $ipAddr;
        $this->port = $port;
        $this->password = $password;
    }
}