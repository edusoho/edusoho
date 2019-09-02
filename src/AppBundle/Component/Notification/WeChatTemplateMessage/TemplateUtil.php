<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

class TemplateUtil
{
    const TEMPLATE_HOMEWORK_OR_TESTPAPER_REVIEW = 'homeworkOrTestPaperReview';
    const TEMPLATE_HOMEWORK_OR_TESTPAPER_REVIEW_CODE = 'OPENTM414077970';

    const TEMPLATE_COURSE_REMIND = 'courseRemind';
    const TEMPLATE_COURSE_REMIND_CODE = 'OPENTM400833477';

    const TEMPLATE_ASK_QUESTION = 'askQuestion';
    const TEMPLATE_ASK_QUESTION_CODE = 'OPENTM414529612';

    const TEMPLATE_ANSWER_QUESTION = 'answerQuestion';
    const TEMPLATE_ANSWER_QUESTION_CODE = 'OPENTM416215703';

    const TEMPLATE_VIP_EXPIRED = 'vipExpired';
    const TEMPLATE_VIP_EXPIRED_CODE = 'OPENTM401520362';

    const TEMPLATE_LIVE_OPEN = 'liveOpen';
    const TEMPLATE_LIVE_OPEN_CODE = 'TM00080';

    const TEMPLATE_HOMEWORK_RESULT = 'homeworkResult';
    const TEMPLATE_HOMEWORK_RESULT_CODE = 'OPENTM400905764';

    const TEMPLATE_EXAM_RESULT = 'examResult';
    const TEMPLATE_EXAM_RESULT_CODE = 'OPENTM409257668';

    const TEMPLATE_COURSE_UPDATE = 'courseUpdate';
    const TEMPLATE_COURSE_UPDATE_CODE = 'TM408917738';

    const TEMPLATE_COIN_RECHARGE = 'coinRecharge';
    const TEMPLATE_COIN_RECHARGE_CODE = 'OPENTM401498850';

    const TEMPLATE_PAY_SUCCESS = 'paySuccess';
    const TEMPLATE_PAY_SUCCESS_CODE = 'OPENTM417184648';

    public static function templates()
    {
        $templates = array(
            self::TEMPLATE_HOMEWORK_OR_TESTPAPER_REVIEW => array(
                'id' => self::TEMPLATE_HOMEWORK_OR_TESTPAPER_REVIEW_CODE,
                'name' => '作业/试卷批改提醒',
                'setting_modal' => 'admin/wechat-notification/setting-modal/testpaper-or-homework-review-modal.html.twig',
                'content' => 'wechat.notification.template.homework_or_testpaper_need_review',
                'rule' => 'wechat.notification.homework_or_testpaper_setting_conditions',
                'detail' => '{{first.DATA}}<br>时间：{{keyword1.DATA}}<br>作业数目：{{keyword2.DATA}}<br>{{remark.DATA}}',
                'object' => '课程教师',
                'status' => 0,
            ),
            self::TEMPLATE_COURSE_REMIND => array(
                'id' => self::TEMPLATE_COURSE_REMIND_CODE,
                'name' => '上课提醒',
                'content' => 'wechat.notification.template.remind_course',
                'rule' => 'wechat.notification.template.remind_course.rule',
                'detail' => '{{first.DATA}}<br>{{keyword1.DATA}}<br>{{keyword2.DATA}}<br>{{remark.DATA}}',
                'object' => '课程学员',
                'status' => 0,
            ),
            self::TEMPLATE_ASK_QUESTION => array(
                'id' => self::TEMPLATE_ASK_QUESTION_CODE,
                'name' => '答疑提醒',
                'content' => 'wechat.notification.template.ask_question',
                'rule' => 'wechat.notification.template.ask_question.rule',
                'detail' => '尊敬的老师，您的在教课程中有学员发布了提问<br>{{keyword1.DATA}}<br>{{keyword2.Data}}<br>时间{{keyword3.Data}}<br>{{remark.DATA}}',
                'object' => '课程教师',
                'status' => 0,
            ),
            self::TEMPLATE_ANSWER_QUESTION => array(
                'id' => self::TEMPLATE_ANSWER_QUESTION_CODE,
                'name' => '问题回复通知',
                'content' => 'wechat.notification.template.answer_question',
                'rule' => 'wechat.notification.template.answer_question.rule',
                'detail' => '{{first.DATA}}<br>提问时间：{{keyword1.DATA}}<br>回复内容：{{keyword2.DATA}}<br>{{remark.DATA}}',
                'object' => '提问者',
                'status' => 0,
            ),
            self::TEMPLATE_VIP_EXPIRED => array(
                'id' => self::TEMPLATE_VIP_EXPIRED_CODE,
                'name' => '会员到期提醒',
                'setting_modal' => 'admin/wechat-notification/setting-modal/vip-expired-modal.html.twig',
                'content' => 'wechat.notification.template.vip_expired',
                'rule' => 'wechat.notification.template.vip_expired.rule',
                'detail' => '{{first.DATA}}<br>开通时间：{{keyword1.DATA}}<br>到期时间：{{keyword2.DATA}}<br>{{remark.DATA}}',
                'object' => '单个用户',
                'status' => 0,
            ),
            self::TEMPLATE_LIVE_OPEN => array(
                'id' => self::TEMPLATE_LIVE_OPEN_CODE,
                'name' => '直播开课通知',
                'setting_modal' => 'admin/wechat-notification/setting-modal/live-open-modal.html.twig',
                'content' => 'wechat.notification.template.live_start',
                'rule' => 'wechat.notification.template.live_start.rule',
                'detail' => '您好，{{userName.DATA}}。<br>您报名参加的{{courseName.DATA}}将于{{date.DATA}}开课，特此通知。<br>{{remark.DATA}}',
                'object' => '课程学员',
                'status' => 0,
            ),
            self::TEMPLATE_HOMEWORK_RESULT => array(
                'id' => self::TEMPLATE_HOMEWORK_RESULT_CODE,
                'name' => '作业结果通知',
                'content' => 'wechat.notification.template.homework_result',
                'rule' => 'wechat.notification.template.homework_result.rule',
                'detail' => '{{first.DATA}}<br>作业名称：{{keyword1.DATA}}<br>所属课程：{{keyword2.DATA}}<br>辅导老师：{{keyword3.DATA}}<br>{{remark.DATA}}',
                'object' => '作业提交学员',
                'status' => 0,
            ),
            self::TEMPLATE_EXAM_RESULT => array(
                'id' => self::TEMPLATE_EXAM_RESULT_CODE,
                'name' => '考试结果通知',
                'content' => 'wechat.notification.template.exam_result',
                'rule' => 'wechat.notification.template.exam_result.rule',
                'detail' => '{{first.DATA}}<br>考试科目：{{keyword1.DATA}}<br>考试成绩：{{keyword2.DATA}}<br>{{remark.DATA}',
                'object' => '试卷提交学员',
                'status' => 0,
            ),
            self::TEMPLATE_COURSE_UPDATE => array(
                'id' => self::TEMPLATE_COURSE_UPDATE_CODE,
                'name' => '课程更新提醒',
                'content' => 'wechat.notification.template.lesson_add',
                'rule' => 'wechat.notification.template.lesson_add.rule',
                'detail' => '{{first.DATA}}<br>课程名称：{{keyword1.DATA}}<br>课程类别：{{keyword2.DATA}}<br>课程老师：{{keyword3.DATA}}<br>课程时间：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '课程学员',
                'status' => 0,
            ),
            self::TEMPLATE_COIN_RECHARGE => array(
                'id' => self::TEMPLATE_COIN_RECHARGE_CODE,
                'name' => '充值成功通知',
                'content' => 'wechat.notification.template.charge_success',
                'rule' => 'wechat.notification.template.charge_success.rule',
                'detail' => '{{first.DATA}}<br>充值类型：{{keyword1.DATA}}<br>充值订单号：{{keyword2.DATA}}<br>充值金额：{{keyword3.DATA}}<br>充值时间：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 0,
            ),
            self::TEMPLATE_PAY_SUCCESS => array(
                'id' => self::TEMPLATE_PAY_SUCCESS_CODE,
                'name' => '购买成功通知',
                'content' => 'wechat.notification.template.buy_success',
                'rule' => 'wechat.notification.template.buy_success.rule',
                'detail' => '{{first.DATA}}<br>订单详情：{{keyword1.DATA}}<br>订单价格：{{keyword2.DATA}}<br>订单时间：{{keyword3.DATA}}<br>会员到期日：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 0,
            ),
        );

        return $templates;
    }
}
