<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

use Biz\Sms\SmsType;

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

    const TEMPLATE_COMMENT_MODIFY = 'commentModify';
    const TEMPLATE_COMMENT_MODIFY_CODE = 508;

    const TEMPLATE_COURSE_UPDATE = 'courseUpdate';
    const TEMPLATE_COURSE_UPDATE_CODE = 10475;

    const TEMPLATE_COIN_RECHARGE = 'coinRecharge';

    const TEMPLATE_PAY_SUCCESS = 'paySuccess';

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
               'sms_content' => 'wechat.notification.message_subscribe_sms.template.live_start',
               'rule' => 'wechat.notification.template.live_start.rule',
               'detail' => '课程标题：{{thing2.DATA}}<br>时间：{{date5.DATA}}<br>主讲老师：{{thing15.DATA}}<br>备注：{{thing7.DATA}}',
               'smsDetail' => [SmsType::LIVE_NOTIFY => '{{course_title.DATA}}－{{lesson_title.DATA}}将在{{startTime.DATA}}开播！{{url.DATA}}'],
               'kidList' => ['2', '5', '15', '7'],
               'object' => '课程学员',
               'sceneDesc' => '订阅课程开课提醒',
               'role' => 'ROLE_USER',
               'status' => 0,
           ],
           self::TEMPLATE_HOMEWORK_RESULT => [
               'id' => self::TEMPLATE_HOMEWORK_RESULT_CODE,
               'name' => 'wechat.notification.template_name.homework_result',
               'content' => 'wechat.notification.message_subscribe_template.homework_result',
               'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_student',
               'sms_content' => 'wechat.notification.message_subscribe_sms.template.homework_result',
               'rule' => 'wechat.notification.template.homework_result.rule',
               'detail' => '标题：{{thing2.DATA}}<br>作业情况：{{thing3.DATA}}<br>评语：{{thing8.DATA}}',
               'smsDetail' => [SmsType::EXAM_REVIEW => '您的{{course_title.DATA}}-{{lesson_title.DATA}}已被老师批阅，快来看看吧！'],
               'kidList' => ['2', '3', '8'],
               'object' => '作业提交学员',
               'sceneDesc' => '作业批改完成通知',
               'role' => 'ROLE_USER',
               'status' => 0,
           ],
           self::TEMPLATE_EXAM_RESULT => [
               'id' => self::TEMPLATE_EXAM_RESULT_CODE,
               'name' => 'wechat.notification.template_name.exam_result',
               'content' => 'wechat.notification.message_subscribe_template.exam_result',
               'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_student',
               'sms_content' => 'wechat.notification.message_subscribe_sms.template.exam_result',
               'rule' => 'wechat.notification.template.exam_result.rule',
               'detail' => '考试名称：{{thing1.DATA}}<br>分数：{{number4.DATA}}<br>温馨提示：{{thing6.DATA}}',
               'smsDetail' => [SmsType::EXAM_REVIEW => '您的{{course_title.DATA}}-{{lesson_title.DATA}}已被老师批阅，快来看看吧！'],
               'kidList' => ['1', '4', '6'],
               'object' => '试卷提交学员',
               'sceneDesc' => '考试成绩通知',
               'role' => 'ROLE_USER',
               'status' => 0,
           ],
            self::TEMPLATE_COMMENT_MODIFY => [
                'id' => self::TEMPLATE_COMMENT_MODIFY_CODE,
                'name' => 'wechat.notification.template_name.comment_modify',
                'content' => 'wechat.notification.message_subscribe_template.comment_modify',
                'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_student',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.comment_modify',
                'rule' => 'wechat.notification.template.comment_modify.rule',
                'detail' => '相关课程：{{thing1.DATA}}<br>点评类别：{{thing12.DATA}}<br>作业标题：{{thing10.DATA}}<br>点评内容：{{thing2.DATA}}<br>点评时间：{{time4.DATA}}',
                'smsDetail' => [SmsType::COMMENT_MODIFY_NOTIFY => '课程：《{{course.DATA}}》-学习任务：[{{task.DATA}}{{type.DATA}}任务]的教师评语已修改，点击查看[{{url.DATA}}]'],
                'kidList' => ['1', '12', '10', '2', '4'],
                'object' => '作业/考试提交学员',
                'sceneDesc' => '考试和作业的评语',
                'role' => 'ROLE_USER',
                'status' => 0,
            ],
           self::TEMPLATE_COURSE_UPDATE => [
               'id' => self::TEMPLATE_COURSE_UPDATE_CODE,
               'name' => 'wechat.notification.template_name.lesson_add',
               'content' => 'wechat.notification.message_subscribe_template.lesson_add',
               'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_student',
               'sms_content' => 'wechat.notification.message_subscribe_sms.template.lesson_add',
               'rule' => 'wechat.notification.message_subscribe_template.lesson_add',
               'detail' => '课程：{{thing1.DATA}}<br>老师：{{thing4.DATA}}<br>时间：{{time2.DATA}}<br>备注：{{thing3.DATA}}',
               'smsDetail' => [
                   SmsType::LIVE_NOTIFY => '{{course_title.DATA}}－{{lesson_title.DATA}}将在{{startTime.DATA}}开播！{{url.DATA}}',
                   SmsType::TASK_PUBLISH => '您的{{course_title.DATA}}－{{lesson_title.DATA}}已发布！{{url.DATA}}',
               ],
               'kidList' => ['1', '4', '2', '3'],
               'object' => '课程学员',
               'sceneDesc' => '课程更新通知',
               'role' => 'ROLE_USER',
               'status' => 0,
           ],
//            self::TEMPLATE_HOMEWORK_OR_TESTPAPER_REVIEW => [
//                'name' => 'wechat.notification.template_name.homework_or_testpaper_need_review',
//                'setting_modal' => 'admin/wechat-notification/setting-modal/testpaper-or-homework-review-modal.html.twig',
//                'setting_modal_v2' => 'admin-v2/operating/wechat-notification/setting-modal/testpaper-or-homework-review-modal.html.twig',
//                'content' => '',
//                'position' => '',
//                'sms_content' => 'wechat.notification.template.homework_or_testpaper_need_review',
//                'rule' => 'wechat.notification.homework_or_testpaper_setting_conditions',
//                'smsDetail' => [
//                    SmsType::REVIEW_NOTIFY => '尊敬的老师，您今日仍有作业/试卷未批改<br> 时间：{{day.DATA}}<br> 数目：{{num.DATA}}<br> 请及时批改。',
//                ],
//                'detail' => '',
//                'object' => '课程教师',
//                'role' => '',
//                'status' => 0,
//            ],
//           self::TEMPLATE_COURSE_REMIND => [
//               'name' => 'wechat.notification.template_name.remind_course',
//               'setting_modal' => 'admin/wechat-notification/setting-modal/course-remind-modal.html.twig',
//               'setting_modal_v2' => 'admin-v2/operating/wechat-notification/setting-modal/course-remind-modal.html.twig',
//               'content' => '',
//               'position' => '',
//               'sms_content' => 'wechat.notification.template.remind_course',
//               'rule' => 'wechat.notification.template.remind_course.rule',
//               'smsDetail' => [
//                   SmsType::STUDY_NOTIFY => '今日也要坚持学习哦<br> 课程：{{title.DATA}}<br> 时间：{{day.DATA}}<br> 学习进度：{{progress.DATA}}',
//               ],
//               'detail' => '',
//               'object' => '课程学员',
//               'role' => '',
//               'status' => 0,
//           ],
            self::TEMPLATE_ASK_QUESTION => [
                'id' => self::TEMPLATE_ASK_QUESTION_CODE,
                'name' => 'wechat.notification.template_name.ask_question',
                'content' => 'wechat.notification.message_subscribe_template.ask_question',
                'position' => 'wechat.notification.message_subscribe_template.position.lesson_page_teacher',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.ask_question',
                'rule' => 'wechat.notification.template.ask_question.rule',
                'detail' => '课程名称：{{thing4.DATA}}<br>提问问题：{{thing2.DATA}}<br>提问时间：{{date3.DATA}}',
                'smsDetail' => [
                    SmsType::ANSWER_QUESTION_NOTIFY => '尊敬的老师，《{{title.DATA}}》中有学员提问<br> 申请人：{{user.DATA}}<br> 问题内容：{{question.DATA}}<br> 时间：{{time.DATA}}',
                ],
                'kidList' => ['4', '2', '3'],
                'object' => '<br>课程/班级教师，<br>助教，班主任',
                'sceneDesc' => '学生提问提醒',
                'role' => 'ROLE_TEACHER',
                'status' => 0,
            ],
            self::TEMPLATE_ANSWER_QUESTION => [
                'name' => 'wechat.notification.template_name.answer_question',
                'content' => '',
                'position' => '',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.answer_question',
                'rule' => 'wechat.notification.template.answer_question.rule',
                'smsDetail' => [
                    SmsType::QUESTION_ANSWER_NOTIFY => '您在{{title.DATA}}中的发表的问题有了新的回答。<br> 提问时间：{{day.DATA}}<br> 回复内容：{{content.DATA}}',
                ],
                'detail' => '',
                'object' => '提问者',
                'role' => '',
                'status' => 0,
            ],
            self::TEMPLATE_COIN_RECHARGE => [
                'name' => 'wechat.notification.template_name.charge_success',
                'content' => '',
                'position' => '',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.charge_success',
                'rule' => 'wechat.notification.template.charge_success.rule',
                'smsDetail' => [
                    SmsType::BUY_NOTIFY => '你已成功{{order_title.DATA}}，支付{{totalPrice.DATA}}',
                ],
                'detail' => '',
                'object' => '购买者',
                'role' => '',
                'status' => 0,
            ],
            self::TEMPLATE_PAY_SUCCESS => [
                'name' => 'wechat.notification.template_name.buy_success',
                'content' => '',
                'position' => '',
                'sms_content' => 'wechat.notification.message_subscribe_sms.template.buy_success',
                'rule' => 'wechat.notification.template.buy_success.rule',
                'smsDetail' => [
                    SmsType::BUY_NOTIFY => '你已成功{{order_title.DATA}}，支付{{totalPrice.DATA}}',
                ],
                'detail' => '',
                'object' => '购买者',
                'role' => '',
                'status' => 0,
            ],
        ];
    }
}
