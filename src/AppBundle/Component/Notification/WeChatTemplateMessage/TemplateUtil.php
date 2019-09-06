<?php

namespace AppBundle\Component\Notification\WeChatTemplateMessage;

class TemplateUtil
{
    public static function templates()
    {
        $templates = array(
            'oneHourBeforeLiveOpen' => array(
                'id' => 'TM00080',
                'name' => '直播开课通知(一小时前)',
                'content' => 'wechat.notification.template.live_start',
                'detail' => '您好，{{userName.DATA}}。<br>您报名参加的{{courseName.DATA}}将于{{date.DATA}}开课，特此通知。<br>{{remark.DATA}}',
                'object' => '课程学员',
                'status' => 0,
            ),
            'oneDayBeforeLiveOpen' => array(
                'id' => 'TM00080',
                'name' => '直播开课通知(一天前)',
                'content' => 'wechat.notification.template.live_start',
                'detail' => '您好，{{userName.DATA}}。<br>您报名参加的{{courseName.DATA}}将于{{date.DATA}}开课，特此通知。<br>{{remark.DATA}}',
                'object' => '课程学员',
                'status' => 0,
            ),
            'homeworkResult' => array(
                'id' => 'OPENTM400905764',
                'name' => '作业结果通知',
                'content' => 'wechat.notification.template.homework_result',
                'detail' => '{{first.DATA}}<br>作业名称：{{keyword1.DATA}}<br>所属课程：{{keyword2.DATA}}<br>辅导老师：{{keyword3.DATA}}<br>{{remark.DATA}}',
                'object' => '作业提交学员',
                'status' => 0,
            ),
            'examResult' => array(
                'id' => 'OPENTM409257668',
                'name' => '考试结果通知',
                'content' => 'wechat.notification.template.exam_result',
                'detail' => '{{first.DATA}}<br>考试科目：{{keyword1.DATA}}<br>考试成绩：{{keyword2.DATA}}<br>{{remark.DATA}',
                'object' => '试卷提交学员',
                'status' => 0,
            ),
            'normalTaskUpdate' => array(
                'id' => 'TM408917738',
                'name' => '课程更新提醒（普通任务）',
                'content' => 'wechat.notification.template.lesson_add',
                'detail' => '{{first.DATA}}<br>课程名称：{{keyword1.DATA}}<br>课程类别：{{keyword2.DATA}}<br>课程老师：{{keyword3.DATA}}<br>课程时间：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '课程学员',
                'status' => 0,
            ),
            'liveTaskUpdate' => array(
                'id' => 'TM408917738',
                'name' => '课程更新提醒（直播任务）',
                'content' => 'wechat.notification.template.lesson_add',
                'detail' => '{{first.DATA}}<br>课程名称：{{keyword1.DATA}}<br>课程类别：{{keyword2.DATA}}<br>课程老师：{{keyword3.DATA}}<br>课程时间：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '课程学员',
                'status' => 0,
            ),
            'coinRecharge' => array(
                'id' => 'OPENTM401498850',
                'name' => '充值成功通知',
                'content' => 'wechat.notification.template.charge_success',
                'detail' => '{{first.DATA}}<br>充值类型：{{keyword1.DATA}}<br>充值订单号：{{keyword2.DATA}}<br>充值金额：{{keyword3.DATA}}<br>充值时间：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 0,
            ),
            'paySuccess' => array(
                'id' => 'OPENTM417184648',
                'name' => '购买成功通知',
                'content' => 'wechat.notification.template.buy_success',
                'detail' => '{{first.DATA}}<br>订单详情：{{keyword1.DATA}}<br>订单价格：{{keyword2.DATA}}<br>订单时间：{{keyword3.DATA}}<br>会员到期日：{{keyword4.DATA}}<br>{{remark.DATA}}',
                'object' => '购买者',
                'status' => 0,
            ),
        );

        return $templates;
    }
}
