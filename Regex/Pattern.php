<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Regex;

class Pattern{
    const HAS_DOMAIN_NAME = "([a-zA-Z0-9\-]+(?:\.[a-zA-Z0-9\-]*)*(?:\.[a-zA-Z]*))";
    const IS_DOMAIN_NAME = "^".self::HAS_DOMAIN_NAME."$";
    const HAS_EMAIL = "(?:[a-zA-Z0-9\-]+)@".self::HAS_DOMAIN_NAME;
    const IS_EMAIL = "^".self::HAS_EMAIL."$";
}