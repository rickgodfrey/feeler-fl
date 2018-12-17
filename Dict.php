<?php
/**
 * Created by PhpStorm.
 * User: rickguo
 * Date: 17-11-1
 * Time: 下午4:16
 */

namespace rickguo\Fl;

class Dict{
    public static function lettersInLowerCase(){
        return [
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k", "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v", "w", "x", "y", "z"
        ];
    }

    public static function lettersInUpperCase(){
        $letters = self::lettersInLowerCase();

        foreach($letters as &$letter){
            $letter = strtoupper( $letter);
        }
        unset($letter);

        return $letters;
    }

    public static function letters(){
        return Arr::merge(self::lettersInUpperCase(), self::lettersInLowerCase());
    }

    public static function baseNumbers(){
        return [0, 1, 2, 3, 4, 5, 6, 7, 8, 9];
    }
}