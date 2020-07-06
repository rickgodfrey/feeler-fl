<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

interface IHttpSender{
	public function send($url, $method = Req::GET, $params = [], callable $callback = null);
}