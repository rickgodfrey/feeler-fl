<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-11-25
 * Time: 下午3:16
 */

namespace Fl\Models;

class BbcodeParser{
    protected static $tagRegex = "/\[(?<tagName>\w*)\=?([^\[\]]*)\][^\k<tagName>]*\[\/\k<tagName>\]/im";
    protected static $toParseTags = [
        "url", "img"
    ];

    public static function convertToHtml($content){
        if(!\Fl\Str::isAvailable($content)){
            return "";
        }
    }
}