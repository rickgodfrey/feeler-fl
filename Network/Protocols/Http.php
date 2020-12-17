<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols;

use Feeler\Fl\Network\Protocols\Http\HttpReceiver;
use Feeler\Fl\Network\Protocols\Http\HttpSender;

class Http
{
    /**
     * @return mixed
     */
    public static function receiverInstance():HttpReceiver{
        return HttpReceiver::instance();
    }

    /**
     * @param array $headers
     * @param string $basicAuth
     * @return HttpSender
     */
    public static function senderInstance():HttpSender{
        return HttpSender::instance();
    }
}