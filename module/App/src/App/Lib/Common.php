<?php

/**
 * This file is part of the Omega package.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace App\Lib;

class Common
{
    const APP_ID = 1;
    const APP_KEY = "DAB578EC-6C01-4180-939A-37E6BE8A81AF";
    const APP_IV = "117A5C0F";

    const REGISTER_CMD = 1;
    const AUTH_CMD = 2;
    const SEARCH_CMD = 3;
    const ORDER_CMD = 4;
    const HIS_ORDERS_CMD = 5;

    const M6SERVER = 'http://o.m6fresh.com/ws/app.ashx';

    const M6FLAG_Success = 1;

    static public function EncryptAppReqData($cmd, $data)
    {
        $raw_req_data = Common::APP_KEY. (string)$cmd. (string)Common::APP_ID. $data. Common::APP_IV;
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