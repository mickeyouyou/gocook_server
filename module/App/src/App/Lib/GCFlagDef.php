<?php

/**
 * This file is part of the GoCook project.
 * @copyright Copyright (c) 2010-2013 BadPanda Inc.
 */

namespace App\Lib;

final class GCFlag {
    private function __construct() {}

    // is my watch
    const E_IsMyWatch = 0;                      // 是我的关注
    const E_NotMyWatch = 1;                     // 不是我的关注

    // is collected
    const E_IsCollected = 0;                    // 已经收藏
    const E_NotCollected = 1;                   // 未收藏

    // like
    const E_Liked = 0;                          // 赞
    const E_UnLiked = 1;                        // 未赞

    // has delay the sales                      // 是否延期成功
    const E_IsDelayed = 0;                      // 延期成功
    const E_NotDelayed = 1;                     // 延期未成功
    const E_NothingDelay = 2;                   // 已经延期过

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
    const GC_KeywordNull = 105;                 // 查询的keyword为空
    const GC_GetParamInvalid = 106;             // get参数不正确

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
    const GC_LoginError = 212;                  // 某种情况下登录失败（需要再细化）
    const GC_AccountUserInfoError = 213;        // user info不存在，理论上是不可能会这样的
    const GC_NickNameInvalid = 214;             // 用户名不合法

    const GC_ProductInvalid = 301;              // 商品不存在或无效错误
    const GC_OrderAccountInvalid = 302;         // 订购失败,客户不存在或无效
    const GC_OrderInvalid = 303;                // 订购失败,订单已经存在且订单状态错误

    const GC_RecipeNotExist = 401;              // 不存在该菜谱
    const GC_RecipeNotBelong2U = 402;           // 此菜谱不属于当前用户
    const GC_AddRecipeCollectionError = 403;    // 加入收藏失败
    const GC_AlreadyCollectRecipe = 404;        // 已经收藏该菜谱
    const GC_NotMyCollectRecipe = 405;          // 该菜谱本人未收藏
    const GC_CommentOnRecipeFailed = 406;       // 评论失败
    const GC_AlreadyLikedRecipe = 407;          // 已经赞过该菜谱
    const GC_NotLikedRecipe = 408;              // 该菜谱本人未赞过

    const GC_RecipeNameInvalid = 409;           // 菜谱名不合法或者为空
    const GC_RecipeMaterialInvalid = 410;       // 菜谱材料不合法或者为空
    const GC_RecipeStepInvalid = 410;           // 菜谱步骤不合法或者为空
    const GC_RecipeCoverInvalid = 410;          // 菜谱封面不合法或者为空

    const GC_AlreadyWatchUser = 501;            // 已经关注过此用户
    const GC_NotMyWatchUser = 502;              // 并未关注此用户

    const GC_DelayRecordNotValid = 601;         //无该延期券
}