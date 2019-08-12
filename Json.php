<?php

namespace Feeler\Fl;

class Json{
    public static function decode($json, $assoc = false, $depth = 512, $options = 0){
        $rs = json_decode($json, $assoc, $depth, $options);

        if($rs == null){
            $rs = [];
        }

        return $rs;
    }
}