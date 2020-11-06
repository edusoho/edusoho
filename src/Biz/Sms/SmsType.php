<?php

namespace Biz\Sms;

class SmsType
{
    /**
     * 导入用户云短信模版id  您的账号已创建成功，您可使用手机号来登录网校： $url。密码：$password。为了您的账号安全，请在登录后及时修改密码
     */
    const IMPORT_USER = 1746;

    /**
     * 邀请注册奖励不足短信模版id  您设置的$activity_name活动奖励$reward_name仅剩 $remain 张，请及时关注剩余优惠券数量。
     */
    const INVITE_REWARD_INSUFFICIENT = 1748;

    const INVITE_REWARD_EXHAUST = 1749;

    /**
     * 用户注销短信模板id  尊敬的用户，您的帐号已成功注销，很遗憾不能再为您继续提供服务，感谢您对 $schoolName的支持
     */
    const USER_DESTROYED = 2040;

    /**
     * 用户注销拒绝短信模板id  尊敬的用户，您的帐号注销申请审核未通过，理由：$reason
     */
    const USER_REJECT_DESTROYED = 2032;

    /**
     * 验证码短信模板id  您的验证码是：${verify}(请勿泄露),此验证码30分钟内有效
     */
    const VERIFY_CODE = 289;

    /**
     * 考试批阅短信模板id  您的${course_title}-${lesson_title}已被老师批阅，快来看看吧！
     */
    const EXAM_REVIEW = 295;

    /**
     * 购买通知短信模板id  你已成功${order_title}，支付${totalPrice}
     */
    const BUY_NOTIFY = 296;

    /**
     * 班级发布通知短信模板id  同学们，${classroom_title}现已开始报名！${url}
     */
    const CLASSROOM_PUBLISH = 291;

    /**
     * 课程发布通知短信模板id  同学们，${course_title}现已开始报名！${url}
     */
    const COURSE_PUBLISH = 291;
}
