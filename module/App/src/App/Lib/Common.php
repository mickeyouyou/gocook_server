<?php

/**
 * This file is part of the GoCook project.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace App\Lib;

use App\Lib\CommonDef;

class Common
{
    /**************************************************************
     *
     * 加密客户端发来的内容
     * @param int $cmd
     * @param string $data
     * @return string 加密后的字符串
     * @access public
     *
     *************************************************************/
    static public function EncryptAppReqData($cmd, $data)
    {
        $raw_req_data = CommonDef::APP_KEY. (string)$cmd. (string)CommonDef::APP_ID. $data. CommonDef::APP_IV;
        return base64_encode(md5($raw_req_data,true));
    }

    /**************************************************************
     *
     * 使用特定function对数组中所有元素做处理
     * @param string &$array  要处理的字符串
     * @param string $function 要执行的函数
     * @return boolean $apply_to_keys_also  是否也应用到key上
     * @access public
     *
     *************************************************************/
    static function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
}