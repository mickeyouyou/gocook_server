<?php

/**
 * This file is part of the GoCook project.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace App\Lib;

final class CommonDef
{
    private function __construct() {}

    // app
    const APP_ID = 1;
    const APP_KEY = "DAB578EC-6C01-4180-939A-37E6BE8A81AF";
    const APP_IV = "117A5C0F";

    // m6 cmd
    const REGISTER_CMD = 1;
    const AUTH_CMD = 2;
    const SEARCH_CMD = 3;
    const ORDER_CMD = 4;
    const HIS_ORDERS_CMD = 5;

    // m6 server
    const M6SERVER = 'http://o.m6fresh.com/ws/app.ashx';
}

final class GCFlag {
    private function __construct() {}

    // go_cook return operator code
    const GC_Success = 0;                       // 成功
    const GC_Failed = 1;                        // 失败


    // go_cook return error code
    const GC_NoErrorCode = 0;                   // 无错误码
    const GC_CommonError = 100;                 // 一般错误(正常情况是不应该出现的错误)

    const GC_NoMobileDevice = 101;              // 非移动设备
    const GC_AuthAccountInvalid = 102;          // 未授权用户
    const GC_NoPost = 103;                      // 不是post上传
    const GC_PostInvalid = 104;                 // 上传post不合法

    const GC_TelExist = 201;                    // 电话号码重复
    const GC_NickNameExist = 202;               // 昵称重复
    const GC_M6ServerConnError = 203;           // 甲方服务器错误(连接错误)
    const GC_M6ServerError = 204;               // 甲方服务器错误(逻辑错误，go_cook校验服务器返回结果错误)
    const GC_RegError = 205;                    // 注册失败
    const GC_AccountExist = 206;                // 206: 账号已存在
    const GC_AccountNotExist = 207;             // 账号不存在
    const GC_PasswordInvalid = 208;             // 密码错误
    const GC_ChangeAvatarError = 209;           // 修改头像失败（保存时出错）
    const GC_AvatarSizeTooSmall = 210;          // 头像文件小于1k
    const GC_NoPostAvatarFile = 211;            // 上传的post中不包含avatar

    const GC_RecipeNotExist = 401;              // 不存在该菜谱
    const GC_RecipeNotBelong2U = 402;           // 此菜谱不属于当前用户
}

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