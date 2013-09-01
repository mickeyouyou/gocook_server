<?php

/**
 * This file is part of the GoCook project.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace App\Lib;

final class M6Flag {
    private function __construct() {}

    // m6 return code
    const M6FLAG_Success = 1;                   // 成功
    const M6FLAG_Fail = -1;                     // 失败
    const M6FLAG_MD5_Error = -10;               // Md5 验证失败
    const M6FLAG_Reg_ActExist = -2;             // 注册失败,帐号已经存在
    const M6FLAG_Auth_ActInvalid = -3;          // 认证失败,帐号不存在或无效
    const M6FLAG_Auth_PswInvalid = -4;          // 认证失败,密码错误
    const M6FLAG_Order_ActInvalid = -5;         // 订购失败,客户不存在或无效
    const M6FLAG_Search_ActInvalid = -6;        // 查询失败,客户不存在或无效
    const M6FLAG_Arg_Error = -11;               // 参数数据错误
    const M6FLAG_Product_Invalid = -12;         // 商品不存在或无效错误
    const M6FLAG_Order_Invalid = -13;           // 订购失败,订单已经存在且订单状态错误
    const M6FLAG_Search_ArgError = -15;         // 查询参数错误
}