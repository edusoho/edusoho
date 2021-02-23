<?php

namespace OpenLivePlugin\Biz\OpenLivePlatform;

class ErrorCodeTrans
{
    protected $errorCodeToMsg = [
        40370000 => '学生不存在',
        40370001 => '手机格式不正确',
        40370002 => '该手机已绑定',
        40370003 => '该手机已被其他用户绑定',
        40370004 => '验证码已失效',
        40370005 => '超过可尝试次数',
        40370006 => '验证码错误',
        40370007 => '单个手机间隔时间内无法重复发送校验码',
        40370008 => '用户token已过期',
        40360009 => '错误的请求token',
        40370010 => '直播间不存在',
        40370011 => '学生不存在',
        40370012 => '直播未结束',
        40370013 => '手机号达到单日可发送上限',
        40370014 => '创建通知任务失败',
        40370015 => '创建统计任务失败',
        40370016 => '创建直播间失败，存在未结算直播间或账户已欠费',
        40370017 => '进入房间失败，无法找到对应的直播间',
        40370018 => '调用直播供应商失败'
    ];

    public function transCodeToMsg($code)
    {
        if (!array_key_exists($code, $this->errorCodeToMsg)) {
            return '';
        }

        return $this->transCodeToMsg($code);
    }
}
