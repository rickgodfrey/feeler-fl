<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-5-5
 * Time: 下午2:19
 */

namespace Fl\Interfaces;

interface IHttpSender{
	public function send($url, $params = [], $headers = [], $basicAuth = "");
}