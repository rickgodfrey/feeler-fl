<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Base\Singleton;
use Feeler\Base\Arr;
use Feeler\Base\GlobalAccess;

class RequestReceiver extends Singleton {
    const GET = "GET";
    const POST = "POST";
    const PUT = "PUT";
    const DELETE = "DELETE";

    protected $input;

    public function filter($data, $type = HttpFilter::HTML_ESCAPED, int $len = -1){
        return HttpFilter::act($data, $type, $len);
    }

    public function get($field = null, $type = HttpFilter::HTML_ESCAPED, int $len = -1){
        if($field === null){
            return $this->filter(GlobalAccess::get(), $type, $len);
        }

        $value = Arr::getVal(GlobalAccess::get(), $field);
        $value = $this->filter($value, $type, $len);

        return $value;
    }

    public function post($field = null, $type = HttpFilter::HTML_ESCAPED, int $len = -1){
        if($field === null){
            return $this->filter(GlobalAccess::post(), $type, $len);
        }

        $value = Arr::getVal(GlobalAccess::post(), $field);
        $value = $this->filter($value, $type, $len);

        return $value;
    }

    public function both($field = null, $type = HttpFilter::HTML_ESCAPED, int $len = -1){
        if($field == null){
            return Arr::mergeByKey($this->get(null, $type, $len), $this->post(null, $type, $len));
        }

        $rs = $this->get($field, $type, $len) or $rs = $this->post($field, $type, $len);

        return $rs;
    }

    public function input($field = null, $type = HttpFilter::HTML_ESCAPED, int $len = -1){
        if(!$this->input){
            parse_str(file_get_contents("php://input"), $this->input);
        }

        if(!$this->input){
            return null;
        }

        if($field === null){
            return $this->input;
        }

        $value = Arr::getVal($this->input, $field);
        $value = $this->filter($value, $type, $len);

        return $value;
    }

    public function requestMethod():string{
        return HttpReceiver::instance()->requestMethod();
    }
}
