<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl\Network\Protocols\Http;

use Feeler\Base\GlobalAccess;

class Url{
    const FORMAT_PARAM = "param";
    const FORMAT_PATH = "path";
    const FORMAT_PATH_INFO = "path_info";

    protected $get;
    protected $reqPath;
    protected $params;

    public static function hasQueryString($string){
        return preg_match("/\/[^\s\?]*\?[^\s]+\=[^\s]+/", $string) ? true : false;
    }
}
