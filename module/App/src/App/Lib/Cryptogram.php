<?php
/**
 * This file is part of the GoCook project.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace App\Lib;

class Cryptogram
{
    /**************************************************************
     * 为字符串添加PKCS7 Padding
     * @param string $source    源字符串
     **************************************************************/
    static public function addPKCS7Padding($source){
        $block = mcrypt_get_block_size('tripledes', 'cbc');
        $pad = $block - (strlen($source) % $block);
        if ($pad <= $block) {
            $char = chr($pad);
            $source .= str_repeat($char, $pad);
        }
        return $source;
    }


    /**************************************************************
     * 去除字符串末尾的PKCS7 Padding
     * @param string $source    带有padding字符的字符串
     **************************************************************/
    static public function stripPKSC7Padding($source){
        $block = mcrypt_get_block_size('tripledes', 'cbc');
        $char = substr($source, -1, 1);
        $num = ord($char);
        if($num > 8){
            return $source;
        }
        $len = strlen($source);
        for($i = $len - 1; $i >= $len - $num; $i--){
            if(ord(substr($source, $i, 1)) != $num){
                return $source;
            }
        }
        $source = substr($source, 0, -$num);
        return $source;
    }

    /**************************************************************
     * 使用3DES加密源数据
     * @param string $oriSource 源数据
     * @param string $key       密钥
     * @param string $defaultIV 加解密向量
     * @return string $result   密文
     **************************************************************/
    static public function encryptByTDES($oriSource, $key, $defaultIV){
        $oriSource = Cryptogram::addPKCS7Padding($oriSource);
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $key, $defaultIV);
        $result = mcrypt_generic($td, $oriSource);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $result;
    }


    /**************************************************************
     * 使用3DES解密密文
     * @param string $encryptedData 密文
     * @param string $key           密钥
     * @param string $defaultIV     加解密向量
     * @return string $result       解密后的原文
     **************************************************************/
    static public function decryptByTDES($encryptedData, $key, $defaultIV){
        $td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($td, $key, $defaultIV);
        $result = mdecrypt_generic($td, $encryptedData);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $result = Cryptogram::stripPKSC7Padding($result);
        return $result;
    }



}
?>