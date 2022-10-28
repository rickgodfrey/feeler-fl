<?php
/**
 * @link https://www.feeler.cc/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license https://www.feeler.cc/license/
 */

namespace Feeler\Fl\Encryption;

use Feeler\Base\Multiton;
use Feeler\Base\Str;
use Feeler\Base\File;

class RSA extends Multiton {
    protected string $privateKey;
    protected string $publicKey;

    public static function getPrivateKeyFromContent(string $keyContent){
        return openssl_pkey_get_private($keyContent);
    }

    public static function getPublicKeyFromContent(string $keyContent){
        return openssl_pkey_get_public($keyContent);
    }

    public static function getPrivateKeyFromFile(string $keyFilePath){
        return self::getPrivateKeyFromContent(File::instace($keyFilePath)->getContent());
    }

    public static function getPublicKeyFromFile(string $keyFilePath){
        return self::getPublicKeyFromContent(File::instace($keyFilePath)->getContent());
    }

    public function setPrivateKeyByContent(string $keyContent):void{
        if(Str::isAvailable($keyContent)){
            $this->privateKey = $keyContent;
        }
    }

    public function setPublicKeyByContent(string $keyContent):void{
        if(Str::isAvailable($keyContent)){
            $this->publicKey = $keyContent;
        }
    }

    public function setPrivateKeyByFile(string $keyFilePath):void{
        $this->setPrivateKeyByContent(self::getPrivateKeyFromFile($keyFilePath));
    }

    public function setPublicKeyByFile(string $keyFilePath):void{
        $this->setPublicKeyByContent(self::getPrivateKeyFromFile($keyFilePath));
    }

    public function sign($content = "") :string|false{
        if (!Str::isString($content)) {
            return false;
        }
        return openssl_sign(
            $content,
            $sign,
            $this->privateKey,
            OPENSSL_ALGO_SHA256
        ) ? base64_encode($sign) : false;
    }

    public function verify(string $content, string $sign) :bool{
        if (!Str::isAvailable($content) || !Str::isAvailable($sign)) {
            return false;
        }
        return (bool)openssl_verify(
            $content,
            base64_decode($sign),
            $this->publicKey,
            OPENSSL_ALGO_SHA256
        );
    }
}
