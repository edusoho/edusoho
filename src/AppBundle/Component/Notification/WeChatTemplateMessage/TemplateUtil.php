<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

class TemplateUtil
{
    public static function templates()
    {
        $templates = array(
            'oneHourBeforeLiveOpen' => array(
                'id' => 'TM00080',
                'name' => '直播课开课通知(一小时前)',
                'content' => 'wechat.notification.template.live_start',
                'object' => '课程学员',
                'status' => 0,
            ),
            'oneDayBeforeLiveOpen' => array(
                'id' => 'TM00080',
                'name' => '直播课开课通知(一天前)',
                'content' => 'wechat.notification.template.live_start',
                'object' => '课程学员',
                'status' => 0,
            ),
            'homeworkResult' => array(
                'id' => 'OPENTM400905764',
                'name' => '作业结果通知',
                'content' => 'wechat.notification.template.homework_result',
                'object' => '作业提交学员',
                'status' => 0,
            ),
            'examResult' => array(
                'id' => 'OPENTM409257668',
                'name' => '考试结果通知',
                'content' => 'wechat.notification.template.exam_result',
                'object' => '试卷提交学员',
                'status' => 0,
            ),
            'normalTaskUpdate' => array(
                'id' => 'TM408917738',
                'name' => '课程更新提醒（普通任务）',
                'content' => 'wechat.notification.template.lesson_add',
                'object' => '课程学员',
                'status' => 0,
            ),
            'liveTaskUpdate' => array(
                'id' => 'TM408917738',
                'name' => '课程更新提醒（直播任务）',
                'content' => 'wechat.notification.template.lesson_add',
                'object' => '课程学员',
                'status' => 0,
            ),
            'coinRecharge' => array(
                'id' => 'OPENTM401498850',
                'name' => '充值成功通知',
                'content' => 'wechat.notification.template.charge_success',
                'object' => '购买者',
                'status' => 0,
            ),
            'paySuccess' => array(
                'id' => 'OPENTM417184648',
                'name' => '购买成功通知',
                'content' => 'wechat.notification.template.buy_success',
                'object' => '购买者',
                'status' => 0,
            ),
        );

        return $templates;
    }
}
