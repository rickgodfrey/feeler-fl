<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

use Feeler\Base\Arr;
use Feeler\Base\Singleton;
use Feeler\Base\Table;
use Feeler\Base\File;
use Feeler\Base\Number;
use Feeler\Base\Str;

class Image extends Singleton {
    const TYPE_MAPPINGS = ["jpg" => "jpeg"];
    const FONT_PATH = ROOT_PATH."requirements/fonts/yahei_mono.ttf";
    const TYPE_PNG = "png";
    const TYPE_JPEG = "jpeg";
    const TYPE_GIF = "gif";
    const TYPE_BMP = "bmp";

    protected static $instance;

    public static function revertType(&$type){
        if(isset(self::TYPE_MAPPINGS[$type])){
            $type = self::TYPE_MAPPINGS[$type];
        }

        return true;
    }

    public static function destroy($res){
        return is_resource($res) ? imagedestroy($res) : false;
    }

    public static function create($width, $height, $color = [255, 255, 255, 100]){
        if(!Number::isNumeric($width) || !Number::isNumeric($height)){
            return false;
        }

        $src = imagecreatetruecolor($width, $height);
        if(!($color = self::getColor($src, $color))){
            return false;
        }

        imagealphablending($src, true);
        imagefill($src, 0, 0, $color);
        imagesavealpha($src, true);

        return is_resource($src) ? $src : false;
    }

    public static function createFromFile($srcFile){
        if(!is_file($srcFile)){
            return false;
        }

        ($type = File::getExt($srcFile)) and self::revertType($type);

        switch($type){
            case "jpeg":
                $src = imagecreatefromjpeg($srcFile);
                break;

            case "png":
                $src = imagecreatefrompng($srcFile);
                break;

            case "gif":
                $src = imagecreatefromgif($srcFile);
                break;

            default:
                return false;
                break;
        }

        imagealphablending($src, true);
        imagesavealpha($src, true);

        return is_resource($src) ? $src : false;
    }

    public static function createFromFiles($files){
        if(!Arr::isAvailable($files)){
            return [];
        }

        $srcs = [];
        foreach($files as $file){
            if(is_file($file) && is_resource($src = self::createFromFile($file)))
                $srcs[md5_file($file)] = $src;
        }

        return $srcs;
    }

    public static function createFromString($string){
        return imagecreatefromstring($string);
    }

    public static function setColor(&$src, $rgb = []){
        if(!is_resource($src) || !Number::isInteric($color = self::getColor($src, $rgb)))
            return false;

        imagealphablending($src, true);
        imagefill($src, 0, 0, $color);
        imagesavealpha($src, true);

        return true;
    }

    public static function binary($res, $type = self::TYPE_PNG){
        if(!is_resource($res) || !in_array($type, [self::TYPE_PNG, self::TYPE_JPEG, self::TYPE_GIF])) {
            return false;
        }

        $rs = File::tempFileCallback(function ($tempFile) use($res, $type){
            if(!$tempFile){
                return false;
            }
            if(!(self::saveAs($tempFile, $res))){
                return false;
            }
            return file_get_contents($tempFile);
        }, ".{$type}");

        return $rs;
    }

    public static function base64Encode($res, $type = self::TYPE_PNG):string {
        if(($binary = self::binary($res, $type)) === false){
            return "";
        }
        $imageInfo = self::getResInfo($res);
        $imageInfo = isset($imageInfo["mime"]) ? $imageInfo["mime"] : "";
        return "data:{$imageInfo};base64,".chunk_split(base64_encode($binary));
    }

    public static function saveAs(string $file, $res){
        if(!is_resource($res) || !Str::isAvailable($file)) {
            return false;
        }
        if(!File::mkdir(File::getPath($file))){
            return false;
        }
        $type = File::getExt($file);
        self::revertType($type);

        switch($type){
            case "jpeg":
                $rs = imagejpeg($res, $file);
                break;

            case "png":
                $rs = imagepng($res, $file);
                break;

            case "gif":
                $rs = imagegif($res, $file);
                break;

            default:
                return false;
                break;
        }

        return $rs ? true : false;
    }

    public static function getColor($src, $rgb){
        if(!Arr::isAvailable($rgb)){
            return null;
        }

        $count = count($rgb);
        if($count != 3 && $count != 4){
            return null;
        }

        if($count == 4){
            [$r, $g, $b, $a] = $rgb;
            $color = imagecolorallocatealpha($src, $r, $g, $b, $a);
        }
        else if($count == 3){
            [$r, $g, $b] = $rgb;
            $color = imagecolorallocate($src, $r, $g, $b);
        }

        return $color;
    }

    public static function fillRectangle(&$src, $rectangle = []){
        if(!is_resource($src) || !Arr::isAvailable($rectangle, ["w", "h", "x", "y", "color"])){
            return false;
        }

        if(!($rectangle["color"] = self::getColor($src, $rectangle["color"]))){
            return false;
        }

        return imagefilledrectangle($src, $rectangle["x"], $rectangle["y"], $rectangle["x"] + $rectangle["w"], $rectangle["y"] + $rectangle["h"], $rectangle["color"]);
    }

    public static function getResInfo($res){
        if(!is_resource($res)){
            return [];
        }
        $imageInfo = File::tempFileCallback(function ($tempFile) use($res){
            if(!imagejpeg($res, $tempFile, 0)){
                return [];
            }
            return getimagesize($tempFile);
        }, ".jpeg");
        if(!$imageInfo){
            return [];
        }

        return [
            "width" => isset($imageInfo[0]) ? $imageInfo[0] : 0,
            "height" => isset($imageInfo[1]) ? $imageInfo[1] : 0,
            "mime" => isset($imageInfo["mime"]) ? $imageInfo["mime"] : "",
        ];
    }

    public static function getFileSize($file){
        return is_file($file) ? getimagesize($file) : null;
    }

    private function _getStringRectangleSize($string, $font){
        $size = [];

        if($string && isset($font["size"]) && Number::isNumeric($font["size"]) && $font["size"] > 0){
            if(!isset($font["angle"]) || !Number::isNumeric($font["angle"])){
                $font["angle"] = 0;
            }

            $size = imageftbbox($font["size"], $font["angle"], self::FONT_PATH, $string);
            $size = [$size[2] - $size[0], $size[7] - $size[1]];
            $size = [abs($size[0]), abs($size[1])];
        }

        return $size;
    }

    public static function getStringRectangleSize($string, $font){
        return self::instance()->_getStringRectangleSize($string, $font);
    }

    private function _sign(&$src, $content, $font = []){
        if(!is_resource($src) || !$content || !Arr::isAvailable($font, ["size", "color", "x", "y"])
            || !Number::isInteric($fontColor = self::getColor($src, $font["color"])) || !($imageSize = self::getResInfo($src)))
        {
            return false;
        }

        if(!($stringRectangleSize = self::getStringRectangleSize($content, $font))){
            return false;
        }

        $fontYOffset = $stringRectangleSize[1] - 3;
        [$font["w"], $font["h"]] = $stringRectangleSize;

        $position = self::calcPosition($font, ["w" => $imageSize["width"], "h" => $imageSize["height"]]);
        $position[1] += $fontYOffset;
        [$font["x"], $font["y"]] = $position;

        if(!isset($font["angle"]) || !Number::isInteric($font["angle"])){
            $font["angle"] = 0;
        }

        $rs = imagefttext($src, $font["size"], $font["angle"], $font["x"], $font["y"], $fontColor, self::FONT_PATH, $content);
        imageantialias($rs, true);

        return $rs ? true : false;
    }

    public static function sign(&$src, $content, $font = []){
        return self::instance()->_sign($src, $content, $font);
    }

    public static function zoom(&$src, $size){
        if(!is_resource($src) || (!Arr::isAvailable($size) && !Number::isFloaric($size)) || !($srcSize = self::getResInfo($src))){
            return false;
        }

        $size = Number::decimalFormat($size, 3);
        if(Number::isNumeric($size)){
            $size = [Number::decimalFormat($srcSize["width"] * $size, 0, false), Number::decimalFormat($srcSize["height"] * $size, 0, false)];
        }

        $dest = self::create($size[0], $size[1]);

        $rs = imagecopyresampled(
            $dest, $src,
            0, 0, 0, 0,
            $size[0], $size[1], $srcSize["width"], $srcSize["height"]
        );

        $src = $dest;
        return $rs;
    }

    public static function makeSquared(&$src){
        if(!($imageSize = self::getResInfo($src))){
            return false;
        }

        if($imageSize["width"] == $imageSize["height"]){
            return true;
        }

        $size = $imageSize["width"] > $imageSize["height"] ? $imageSize["width"] : $imageSize["height"];

        $position = self::calcPosition(["w" => $imageSize["width"], "h" => $imageSize["height"], "x" => "center", "y" => "center"], ["w" => $size, "h" => $size]);

        if(!$position){
            return false;
        }

        $bg = self::create($size, $size);

        $src = self::puzzle(["w" => $size, "h" => $size], [
            [
                "res" => $bg,
                "layer_num" => 1,
                "x" => 0, "y" => 0, "w" => $size, "h" => $size
            ],
            [
                "res" => $src,
                "layer_num" => 2,
                "x" => $position[0], "y" => $position[1], "w" => $imageSize["width"], "h" => $imageSize["height"]
            ]
        ]);

        return true;
    }

    public static function calcPosition($elementInfo, $containerInfo){
        if(!Arr::isAvailable($elementInfo, ["w", "h", "x", "y"]) || !Arr::isAvailable($containerInfo, ["w", "h"])){
            return [];
        }

        $param = [];

        $param["x"] = trim($elementInfo["x"]);
        $param["y"] = trim($elementInfo["y"]);

        $param["x"] = str_replace("　", " ", $param["x"]);
        $param["y"] = str_replace("　", " ", $param["y"]);

        $param["x"] = explode(" ", $param["x"], 2);
        $param["y"] = explode(" ", $param["y"], 2);

        $elementInfo["x"] = 0;
        foreach($param["x"] as $key => $val){
            if(empty($val)){
                continue;
            }

            if(Number::isNumeric($val)){
                $elementInfo["x"] += Number::decimalFormat($val, 1);
            }
            else{
                if($val == "left"){
                    $elementInfo["x"] += 0;
                }
                else if($val == "center"){
                    $elementInfo["x"] += Number::decimalFormat(($containerInfo["w"] - $elementInfo["w"]) / 2, 1);
                }
                else if($val == "right"){
                    $elementInfo["x"] += Number::decimalFormat($containerInfo["w"] - $elementInfo["w"], 1);
                }
            }
        }

        $elementInfo["y"] = 0;
        foreach($param["y"] as $key => $val){
            if(empty($val)){
                continue;
            }

            if(Number::isNumeric($val)){
                $elementInfo["y"] += Number::decimalFormat($val, 1);
            }
            else{
                if($val == "top"){
                    $elementInfo["y"] += 0;
                }
                else if($val == "center"){
                    $elementInfo["y"] += Number::decimalFormat(($containerInfo["h"] - $elementInfo["h"]) / 2, 1);
                }
                else if($val == "bottom"){
                    $elementInfo["y"] += Number::decimalFormat($containerInfo["h"] - $elementInfo["h"], 1);
                }
            }
        }

        return [$elementInfo["x"], $elementInfo["y"]];
    }

    public static function crop(&$src){
        $params = func_get_args();
        if(!is_resource($src) || !isset($params[1]) || !Arr::isAvailable($params[1], ["x", "y", "w", "h"])){
            return false;
        }

        if(isset($params[2]) && !Arr::isAvailable($params[2], ["x", "y", "w", "h"])){
            return false;
        }

        $params = Arr::rebuild($params, ["0", "1", "2"]);

        if(!isset($params[2])){
            $vals = Arr::rebuild($params[1], [
                "destX" => "(int:0){{x}}",
                "destY" => "(int:0){{y}}",
                "destW" => "(int:0){{w}}",
                "destH" => "(int:0){{h}}",
                "color" => "(string:){{color}}",
            ]);
            extract($vals);

            return imagecopyresampled(
                $src, $src,
                0, 0, $destX, $destY,
                $destW, $destH, $destW, $destH
            );
        }
        else{
            $elementInfo = Arr::rebuild($params[1], ["(int:0){{x}}", "(int:0){{y}}", "(int:0){{w}}", "(int:0){{h}}"]);
            $containerInfo = Arr::rebuild($params[2], ["(int:0){{x}}", "(int:0){{y}}", "(int:0){{w}}", "(int:0){{h}}"]);
            if(!($position = self::calcPosition(["w" => $elementInfo["w"], "h" => $elementInfo["h"], "x" => $containerInfo["x"], "y" => $containerInfo["y"]], $containerInfo))){
                return false;
            }

            $containerInfo["x"] = $position[0];
            $containerInfo["y"] = $position[1];

            $elementInfo = Arr::rebuild($elementInfo, [
                "srcX" => "(int:0){{x}}",
                "srcY" => "(int:0){{y}}",
                "srcW" => "(int:0){{w}}",
                "srcH" => "(int:0){{h}}",
            ]);
            extract($elementInfo);

            $containerInfo = Arr::rebuild($containerInfo, [
                "destX" => "(int:0){{x}}",
                "destY" => "(int:0){{y}}",
                "destW" => "(int:0){{w}}",
                "destH" => "(int:0){{h}}",
                "color" => "(string:){{color}}",
            ]);
            extract($containerInfo);

            $prevSrc = $src;
            $src = isset($color) ? self::create($destW, $destH, $color) : self::create($destW, $destH);

            return $src !== false ? imagecopyresampled(
                $src, $prevSrc,
                $destX, $destY, $srcX, $srcY,
                $srcW, $srcH, $srcW, $srcH
            ) : false;
        }
    }

    public static function setBorder(&$src, $borderColor = [0, 0, 0, 1], $borderSize = 1){
        if(!is_resource($src) || !Number::isInteric($borderSize) || $borderSize < 1 || !($imageSize = self::getResInfo($src))){
            return false;
        }

        $containerSize = [$borderSize * 2 + $imageSize["width"], $borderSize * 2 + $imageSize["height"]];

        return self::crop(
            $src,
            ["x" => 0, "y" => 0, "w" => $imageSize["width"], "h" => $imageSize["height"]],
            ["x" => $borderSize, "y" => $borderSize, "w" => $containerSize[0], "h" => $containerSize[1], "color" => $borderColor]
        );
    }

    public static function puzzle($containerInfo, $images){
        if(!Arr::isAvailable($containerInfo) || !isset($containerInfo["w"]) || !isset($containerInfo["h"])){
            return false;
        }

        foreach($images as $key => $image){
            if(!Arr::isAvailable($image, ["res", "layer_num", "x", "y", "w", "h"])){
                unset($images[$key]);
            }
        }

        if(!$images){
            return false;
        }

        Table::sortByField($images, "layer_num", Table::SORT_DESC);

        if(isset($containerInfo["bg_color"])){
            $dest = self::create($containerInfo["w"], $containerInfo["h"], $containerInfo["bg_color"]);
        }
        else{
            $dest = self::create($containerInfo["w"], $containerInfo["h"]);
        }

        foreach($images as $image){
            $position = self::calcPosition($image, $containerInfo);
            $image["x"] = $position[0];
            $image["y"] = $position[1];

            imagecopyresampled(
                $dest, $image["res"],
                $image["x"], $image["y"],
                0, 0,
                $image["w"], $image["h"],
                $image["w"], $image["h"]
            );
        }

        return $dest;
    }
}