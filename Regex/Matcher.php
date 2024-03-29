<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Regex;

use Feeler\Base\BaseClass;
use Feeler\Base\Str;

class Matcher extends BaseClass {
    const DOMAIN_NAME = "domain_name";
    const EMAIL = "email";

    protected static function match($string, string $kind, &$matches, bool $strict = true){
        if(!Str::isAvailable($string)){
            return false;
        }

        switch($kind){
            case self::DOMAIN_NAME:
                if($strict){
                    $pattern = Pattern::IS_DOMAIN_NAME;
                }
                else{
                    $pattern = Pattern::HAS_DOMAIN_NAME;
                }
                break;

            case self::EMAIL:
                if($strict){
                    $pattern = Pattern::IS_EMAIL;
                }
                else{
                    $pattern = Pattern::HAS_EMAIL;
                }
                break;

            default:
                $pattern = "";
                break;
        }

        if(!Str::isAvailable($pattern)){
            return false;
        }

        $matched = preg_match("/{$pattern}/", $string, $matches);

        return $matched;
    }

    public static function matchDomainName($string, &$matches, bool $strict = true){
        return self::match($string, self::DOMAIN_NAME, $matches, $strict);
    }

    public static function matchEmail($string, &$matches, bool $strict = true){
        return self::match($string, self::EMAIL, $matches, $strict);
    }
}