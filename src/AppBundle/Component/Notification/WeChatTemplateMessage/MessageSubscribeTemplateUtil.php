<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

class MessageSubscribeTemplateUtil
{
    const TEMPLATE_HOMEWORK_OR_TESTPAPER_REVIEW = 'homeworkOrTestPaperReview';

    const TEMPLATE_COURSE_REMIND = 'courseRemind';

    const TEMPLATE_ASK_QUESTION = 'askQuestion';
    const TEMPLATE_ASK_QUESTION_CODE = 3541;

    const TEMPLATE_ANSWER_QUESTION = 'answerQuestion';

    const TEMPLATE_LIVE_OPEN = 'liveOpen';
    const TEMPLATE_LIVE_OPEN_CODE = 159;

    const TEMPLATE_HOMEWORK_RESULT = 'homeworkResult';
    const TEMPLATE_HOMEWORK_RESULT_CODE = 1588;

    const TEMPLATE_EXAM_RESULT = 'examResult';
    const TEMPLATE_EXAM_RESULT_CODE = 734;

    const TEMPLATE_COURSE_UPDATE = 'courseUpdate';
    const TEMPLATE_COURSE_UPDATE_CODE = 10475;

    const TEMPLATE_COIN_RECHARGE = 'coinRecharge';

    const TEMPLATE_PAY_SUCCESS = 'paySuccess';

    const TEMPLATE_VIP_EXPIRED = 'vipExpired';

    public static function templates()
    {
        return [
           self::TEMPLATE_LIVE_OPEN => [
               'id' => self::TEMPLATE_LIVE_OPEN_CODE,
               'name' => 'wechat.notification.template_name.live_start',
               'setting_modal' => 'admin/wechat-notification/setting-modal/live-open-modal.html.twig',
               'setting_modal_v2' => 'admin-v2/operating/wechat-notification/setting-modal/live-open-modal.html.twig',
               'content' => 'wechat.notification.message_subscribe_template.live_start',
               'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_student',
               'sms_content' => 'admin.edu_cloud.edu_sms.study_task_notify_tips',
               'rule' => 'wechat.notification.template.live_start.rule',
               'detail' => '{{first.DATA}}<br>课程名称：{{keyword1.DATA}}<br>开课时间：{{keyword2.DATA}}<br>开课地点：{{keyword3.DATA}}<br>联系方式：{{keyword4.DATA}}<br>{{remark.DATA}}',
               'kidList' => ['2', '5', '15', '7'],
               'object' => '课程学员',
               'sceneDesc' => '订阅课程开课提醒',
               'role' => 'student',
               'status' => 0,
           ],
           self::TEMPLATE_HOMEWORK_RESULT => [
               'id' => self::TEMPLATE_HOMEWORK_RESULT_CODE,
               'name' => 'wechat.notification.template_name.homework_result',
               'content' => 'wechat.notification.message_subscribe_template.homework_result',
               'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_student',
               'sms_content' => 'admin.edu_cloud.edu_sms.homework_checked_tips',
               'rule' => 'wechat.notification.template.homework_result.rule',
               'detail' => '{{first.DATA}}<br>作业名称：{{keyword1.DATA}}<br>所属课程：{{keyword2.DATA}}<br>辅导老师：{{keyword3.DATA}}<br>{{remark.DATA}}',
               'kidList' => ['2', '3', '8'],
               'object' => '作业提交学员',
               'sceneDesc' => '作业批改完成通知',
               'role' => 'student',
               'status' => 0,
           ],
           self::TEMPLATE_EXAM_RESULT => [
               'id' => self::TEMPLATE_EXAM_RESULT_CODE,
               'name' => 'wechat.notification.template_name.exam_result',
               'content' => 'wechat.notification.message_subscribe_template.exam_result',
               'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_student',
               'sms_content' => 'admin.edu_cloud.edu_sms.testpaper_checked_tips',
               'rule' => 'wechat.notification.template.exam_result.rule',
               'detail' => '{{first.DATA}}<br>考试科目：{{keyword1.DATA}}<br>考试成绩：{{keyword2.DATA}}<br>{{remark.DATA}}',
               'kidList' => ['1', '4', '6'],
               'object' => '试卷提交学员',
               'sceneDesc' => '考试成绩通知',
               'role' => 'student',
               'status' => 0,
           ],
           self::TEMPLATE_COURSE_UPDATE => [
               'id' => self::TEMPLATE_COURSE_UPDATE_CODE,
               'name' => 'wechat.notification.template_name.lesson_add',
               'content' => 'wechat.notification.message_subscribe_template.lesson_add',
               'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_student',
               'sms_content' => 'wechat.notification.message_subscribe_sms.template.lesson_add',
               'rule' => 'wechat.notification.message_subscribe_template.lesson_add',
               'detail' => '{{first.DATA}}<br>课程名称：{{keyword1.DATA}}<br>课程类别：{{keyword2.DATA}}<br>课程老师：{{keyword3.DATA}}<br>课程时间：{{keyword4.DATA}}<br>{{remark.DATA}}',
               'kidList' => ['1', '4', '2', '3'],
               'object' => '课程学员',
               'sceneDesc' => '课程更新通知',
               'role' => 'student',
               'status' => 0,
           ],
           self::TEMPLATE_VIP_EXPIRED => [
               'name' => 'wechat.notification.template_name.vip_expired',
               'content' => '',
               'position' => '',
               'sms_content' => 'wechat.notification.template.vip_expired',
               'rule' => 'wechat.notification.template.vip_expired.rule',
               'detail' => '',
               'object' => '单个用户',
               'role' => '',
               'status' => 0,
           ],
            self::TEMPLATE_HOMEWORK_OR_TESTPAPER_REVIEW => [
                'name' => 'wechat.notification.template_name.homework_or_testpaper_need_review',
                'setting_modal' => 'admin/wechat-notification/setting-modal/testpaper-or-homework-review-modal.html.twig',
                'setting_modal_v2' => 'admin-v2/operating/wechat-notification/setting-modal/testpaper-or-homework-review-modal.html.twig',
                'content' => '',
                'position' => '',
                'sms_content' => 'wechat.notification.template.homework_or_testpaper_need_review',
                'rule' => 'wechat.notification.homework_or_testpaper_setting_conditions',
                'detail' => '',
                'object' => '课程教师',
                'role' => '',
                'status' => 0,
            ],
           self::TEMPLATE_COURSE_REMIND => [
               'name' => 'wechat.notification.template_name.remind_course',
               'setting_modal' => 'admin/wechat-notification/setting-modal/course-remind-modal.html.twig',
               'setting_modal_v2' => 'admin-v2/operating/wechat-notification/setting-modal/course-remind-modal.html.twig',
               'content' => '',
               'position' => '',
               'sms_content' => 'wechat.notification.template.remind_course',
               'rule' => 'wechat.notification.template.remind_course.rule',
               'detail' => '',
               'object' => '课程学员',
               'role' => '',
               'status' => 0,
           ],
            self::TEMPLATE_ASK_QUESTION => [
                'id' => self::TEMPLATE_ASK_QUESTION_CODE,
                'name' => 'wechat.notification.template_name.ask_question',
                'content' => 'wechat.notification.message_subscribe_template.ask_question',
                'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_teacher',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.ask_question',
                'rule' => 'wechat.notification.template.ask_question.rule',
                'detail' => '{{first.DATA}}<br>申请人：{{keyword1.DATA}}<br>问题内容：{{keyword2.DATA}}<br>时间：{{keyword3.DATA}}<br>{{remark.DATA}}',
                'kidList' => ['4', '1', '2', '3'],
                'object' => '课程/班级教师，助教，班主任',
                'sceneDesc' => '学生提问提醒',
                'role' => 'teacher',
                'status' => 0,
            ],
            self::TEMPLATE_ANSWER_QUESTION => [
                'name' => 'wechat.notification.template_name.answer_question',
                'content' => '',
                'position' => '',
                'sms_content' => 'wechat.notification.template.answer_question',
                'rule' => 'wechat.notification.template.answer_question.rule',
                'detail' => '',
                'object' => '提问者',
                'role' => '',
                'status' => 0,
            ],
            self::TEMPLATE_COIN_RECHARGE => [
                'name' => 'wechat.notification.template_name.charge_success',
                'content' => '',
                'position' => '',
                'sms_content' => 'wechat.notification.template.charge_success',
                'rule' => 'wechat.notification.template.charge_success.rule',
                'detail' => '',
                'object' => '购买者',
                'role' => '',
                'status' => 0,
            ],
            self::TEMPLATE_PAY_SUCCESS => [
                'name' => 'wechat.notification.template_name.buy_success',
                'content' => '',
                'position' => '',
                'sms_content' => 'wechat.notification.template.buy_success',
                'rule' => 'wechat.notification.template.buy_success.rule',
                'detail' => '',
                'object' => '购买者',
                'role' => '',
                'status' => 0,
            ],
        ];
    }
}
