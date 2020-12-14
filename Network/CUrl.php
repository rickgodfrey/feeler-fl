<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

class CUrl {
    protected $handle;

    public function init(){
        $this->handle = curl_init();
    }

    public function send(){
        return curl_exec($this->handle);
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }
}