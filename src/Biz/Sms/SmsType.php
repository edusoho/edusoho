<?php

namespace Biz\Sms;

class SmsType
{
    /**
     * 导入用户云短信模版id  您的账号已创建成功，您可使用手机号来登录网校： ${url}。密码：$password。为了您的账号安全，请在登录后及时修改密码
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
    const COURSE_PUBLISH = 292;

    /**
     * 直播发布通知短信模板id  ${course_title}－${lesson_title}将在${startTime}开播！${url}
     */
    const LIVE_NOTIFY = 294;

    /**
     * 课时发布通知短信模板id  您的${course_title}－${lesson_title}已发布！${url}
     */
    const TASK_PUBLISH = 293;

    /**
     * 会员到期通知短信模板id  亲爱的学员，您购买的会员即将到期 开通时间：${startTime} 到期时间：${endTime} 请及时续费，以免影响您的学习
     */
    const VIP_EXPIRED = 2259;

    /**
     * 作业考试批改通知短信模板id  尊敬的老师，您今日仍有作业/试卷未批改 时间：${day} 数目：${num} 请及时批改。
     */
    const REVIEW_NOTIFY = 2260;

    /**
     * 上课提醒通知短信模板id  今日也要坚持学习哦 课程：${title} 时间：${day} 学习进度：${progress}
     */
    const STUDY_NOTIFY = 2261;

    /**
     * 答疑提醒通知短信模板id  尊敬的老师，《${title}》中有学员提问 申请人：${user} 问题内容：${question} 时间：${time}
     */
    const ANSWER_QUESTION_NOTIFY = 2262;

    /**
     * 问题回复通知短信模板id  您在${title}中的发表的问题有了新的回答。 提问时间：${day} 回复内容：${content}
     */
    const QUESTION_ANSWER_NOTIFY = 2263;

    /**
     * 评语修改通知短信模板id  课程：《{$course}》-学习任务：[{$task}{$type}任务]的教师评语已修改，点击查看[{$url}]
     */
    const COMMENT_MODIFY_NOTIFY = 2312;

    /**
     * 开团成功通知短信模板id 你参与的${title}已开团成功，拼团价为${price}元，需${num}人成团，拼团结束时间为${endAt}，快复制链接邀请微信好友一起参与吧${url}
     */
    const GROUPON_CREATE = 2513;

    /**
     * 参团成功通知短信模板id 你参与的${title}已参团成功，拼团价为${price}元，还剩${remain}人成团，拼团结束时间为${endAt}，快复制链接邀请微信好友一起参与吧${url}
     */
    const GROUPON_JOIN = 2516;

    /**
     * 拼团成功通知短信模板id 你参与的${grouponTitle}已成团，拼团价为${grouponPrice}元，快复制链接到微信中去学习吧${url}
     */
    const GROUPON_SUCCESS = 2511;

    /**
     * 拼团失败通知短信模板id 你参与的${grouponTitle}拼团失败，可复制链接到微信内重新参与拼团${url}
     */
    const GROUPON_FAIL = 2510;

    /**
     * 退款通知短信模板id 你参与的${grouponTitle}拼团失败，发起退款，退款金额为${refundAmount}元，复制链接至微信查看退款详情${url}
     */
    const GROUPON_ORDER_REFUND = 2504;
}
