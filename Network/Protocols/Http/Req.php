<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Base\Arr;
use Feeler\Base\GlobalAccess;

class Req{
    const GET = RequestReceiver::GET;
    const POST = RequestReceiver::POST;
    const PUT = RequestReceiver::PUT;
    const DELETE = RequestReceiver::DELETE;

    public static function filter($data, $type = HttpFilter::HTML_ESCAPED, $len = -1){
        return RequestReceiver::instance()->filter($data, $type, $len);
    }

    public static function get($field = null, $type = HttpFilter::HTML_ESCAPED, $len = -1){
        return RequestReceiver::instance()->get($field, $type, $len);
    }

    public static function post($field = null, $type = HttpFilter::HTML_ESCAPED, $len = -1){
        return RequestReceiver::instance()->post($field, $type, $len);
    }

    public static function both($field = null, $type = HttpFilter::HTML_ESCAPED, $len = -1){
        return RequestReceiver::instance()->both($field, $type, $len);
    }

    public static function input($field = null, $type = HttpFilter::HTML_ESCAPED, $len = -1){
        return RequestReceiver::instance()->input($field, $type, $len);
    }

    public static function method():string{
        return RequestReceiver::instance()->requestMethod();
    }
}
