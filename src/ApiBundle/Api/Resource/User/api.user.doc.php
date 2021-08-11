<?php

/**
 * @OA\Schema(
 *     schema="user.simple",
 *     title="user.simple",
 *     description="Simple用户对象",
 *     @OA\Property(property="id",description="用户ID", type="integer"),
 *     @OA\Property(property="nickname",description="nickname", type="string"),
 *     @OA\Property(property="title",description="头衔", type="string"),
 *     @OA\Property(property="uuid",description="用户UUID", type="string"),
 *     @OA\Property(property="destroyed",description="是都已注销，0|1", type="integer"),
 *     @OA\Property(
 *         property="avatar",
 *         description="是都已注销，0|1",
 *         type="integer",
 *         @OA\Property(property="small",description="小图", type="string"),
 *         @OA\Property(property="middle",description="中图", type="string"),
 *         @OA\Property(property="large",description="大图", type="string"),
 *     ),
 *     @OA\Property(property="weChatQrCode",description="微信二维码地址",type="string"),
 * )
 *
 * @OA\Schema(
 *     schema="user.public",
 *     title="user.public",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/user.simple"),
 *         @OA\Schema(
 *             @OA\Property(property="about",description="个人介绍",type="string"),
 *             @OA\Property(property="faceRegistered",description="人脸识别是否录入（注册）0|1",type="integer"),
 *         )
 *     }
 * )
 *
 * @OA\Schema(
 *     schema="user.authenticated",
 *     title="user.authenticated",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/user.public"),
 *         @OA\Schema(
 *             @OA\Property(property="email",description="邮箱", type="string"),
 *             @OA\Property(property="locale",description="语言：zh_CN|en", type="string"),
 *             @OA\Property(property="uri",description="-",type="string"),
 *             @OA\Property(property="type",description="注册类型", type="string"),
 *             @OA\Property(property="roles",description="用户角色（组）", type="array",@OA\Items()),
 *             @OA\Property(property="promotedSeq",description=""),
 *             @OA\Property(property="locked",description=""),
 *             @OA\Property(property="currentIp",description=""),
 *             @OA\Property(property="gender",description=""),
 *             @OA\Property(property="iam",description=""),
 *             @OA\Property(property="city",description=""),
 *             @OA\Property(property="qq",description=""),
 *             @OA\Property(property="signature",description=""),
 *             @OA\Property(property="company",description=""),
 *             @OA\Property(property="job",description=""),
 *             @OA\Property(property="school",description=""),
 *             @OA\Property(property="class",description=""),
 *             @OA\Property(property="weibo",description=""),
 *             @OA\Property(property="weixin",description=""),
 *             @OA\Property(property="isQQPublic",description=""),
 *             @OA\Property(property="isWeixinPublic",description=""),
 *             @OA\Property(property="isWeiboPublic",description=""),
 *             @OA\Property(property="following",description=""),
 *             @OA\Property(property="follower",description=""),
 *             @OA\Property(property="verifiedMobile",description=""),
 *             @OA\Property(property="promotedTime",description=""),
 *             @OA\Property(property="lastPasswordFailTime",description=""),
 *             @OA\Property(property="loginTime",description=""),
 *             @OA\Property(property="approvalTime",description=""),
 *             @OA\Property(property="vip",description=""),
 *             @OA\Property(property="token",description=""),
 *             @OA\Property(property="havePayPassword",description=""),
 *             @OA\Property(property="fingerPrintSetting",description=""),
 *         )
 *     }
 * )
 */