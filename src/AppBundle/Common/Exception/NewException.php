<?php

namespace AppBundle\Common\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Topxia\Service\Common\ServiceKernel;

class NewException extends HttpException
{
    const INTERNAL_ERROR = 500;

    const FORBID = 430;

    const NOTFOUND = 404;

    //错误码
    // 400 不合法
    // 401 类型错误
    // 410 缺少参数
    // 420 超时失效
    // 404 找不到
    // 430 权限,需要
    // 440 某些参数为空
    // 450 字段内容超过限制
    // 500 内部报错

    //对象 
    // 01 用户
    // 02 课程
    // 03 计划
    // 04 话题
    // 05 小组
    // 06 资讯
    // 07 订单
    // 08 账务

    // 业务
    // 无业务 00
    // 管理 01
    // 删除 02
    // 修改 03
    // 提交 04
    // 登录 05
    // 点赞 06

    const LIMIT_USER_LOGIN = 4500105;

    const FORBID_COURSE_MANAGE = 4300201;

    const ILLEGAL_COURSE_MANAGE = 4000201;

    const NOTFOUND_ARTICLE = 4040600;

    const NOTFOUND_USER = 4040100;

    const NOTFOUND_COURSE = 4040300;

    const FORBID_ARTICLE_LIKE = 4300606;

    public static $statusInfo = array(
        4300201 => 'exception.course_set.manage',
        4000201 => 'exception.course.illegal_manage',
        4040600 => 'exception.article.notfound',
        4040100 => 'exception.user.unlogin',
        4500105 => '您已经被限制登录',
        4040300 => 'exception.course.notfound',
        4300606 => '用户还未登录,不能点赞。',
    );



    private function trans($message, $arguments = array())
    {
        return ServiceKernel::instance()->trans($message, $arguments);
    }
}
