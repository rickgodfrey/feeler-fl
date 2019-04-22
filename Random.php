<?php
/**
 * @link http://www.feeler.top/
 * @copyright Copyright (c) 2019 Rick Guo
 * @license http://www.feeler.top/license/
 */

namespace Feeler\Fl;

class Random{
	public static function uuid($whole = false){
		$id = strtolower(md5(uniqid(mt_rand(0, (double)microtime() * 1000000), true)));
		
		if($whole){
			$uuid = 
				substr($id, 0, 8)."-".
				substr($id, 8, 4)."-".
				substr($id, 12, 4)."-".
				substr($id, 16, 4)."-".
				substr($id, 20, 12)
			;
		}
		else
			$uuid = $id;
		
		return $uuid;
	}
	
	public static function num($len = 6){
		$nums = array(6, 8, 1, 7, 2, 5, 3, 9, 0, 4);
		
		if($len > 10) $len = 10;
		else if($len < 1) $len = 1;
		
		shuffle($nums);
		shuffle($nums);
		
		$num = implode("", $nums);
		$num = substr($num, 0, $len);
		
		return (string)$num;
	}

    /**  生成随机名称
     * @param $length 名称长度
     * @param int $numeric
     * @return string
     */
    public static function random($length, $numeric = 0) {
        $seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
        $seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
        $hash = '';
        $max = strlen($seed) - 1;
        for($i = 0; $i < $length; $i++) {
            $hash .= $seed{mt_rand(0, $max)};
        }
        return $hash;
    }
}
