<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

class Security{
    /**
     * @param $password
     * @throws Exception
     * @brief Check the password's safety
     */
    public static function checkPasswordSafety($password){
        if(!Str::isAvailable($password)){
            throw new \Fl\Exception("901", "empty password is not allowed");
        }

        $passwordLen = strlen($password);

        //the legal password can not be less than 6 chars or more than 16 chars
        if ($passwordLen < 6 || $passwordLen > 16) {
            throw new \Fl\Exception("902", "the legal password can not be less than 6 chars or more than 16 chars");
        }

        //To check the password's chars are all in the ascii charecter set
        if (!\Fl\Validator::testAscii($password)) {
            throw new \Fl\Exception("903", "the password's chars have illegal charecter");
        }

        //Check either is week password
        if (preg_match("/^(?:(?:[0-9]*)|(?:[a-z]*))$/", $password)) {
            throw new \Fl\Exception("904", "week password");
        }

        return true;
    }
}