<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Interfaces;

interface IHttpSender{
	public function send($url, $params = [], $headers = [], $basicAuth = "");
}