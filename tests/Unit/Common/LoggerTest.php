<?php

namespace Tests\Unit\Common;

use Biz\BaseTestCase;
use Biz\Common\Logger;

class LoggerTest extends BaseTestCase
{
    public function testGetLogModuleDict()
    {
        $result = Logger::getLogModuleDict();

        $this->assertEquals(array(
            'user' => '用户',
            'course' => '课程',
            'classroom' => '班级',
            'group' => '小组话题',
            'upload_file' => '文件',
            'order' => '订单',
            'article' => '资讯',
            'category' => '栏目',
            'content' => '资讯内容',
            'notify' => '通知',
            'sms' => '短信',
            'tag' => '标签',
            'system' => '系统设置',
            'crontab' => '定时任务',
            'marker' => '驻点',
            'vip' => '会员',
            'coin' => '虚拟币',
            'coupon' => '优惠码',
            'money_card' => '学习卡',
            'discount' => '打折活动',
            'exercise' => '练习',
            'homework' => '作业',
            'question_plus' => '题库增强版',
            'announcement' => '公告',
            'open_course' => '公开课',
            'live' => '直播',
        ), $result);
    }

    public function testPluginModuleConfig()
    {
        $result = Logger::pluginModuleConfig();

        $this->assertEquals(array(
            'vip' => array(
                    'create_member' => '新增会员',
                    'renew_member' => '续费会员',
                    'upgrade_member' => '升级会员',
                    'delete_member' => '删除会员',
                    'update_member' => '编辑会员',
                    'create_level' => '添加等级',
                    'update_level' => '修改等级',
                    'on_level' => '开启加入',
                    'off_level' => '关闭加入',
                    'delete_level' => '删除等级',
                    'exportCsv' => '导出',
                    'update_setting' => '更新设置',
                ),
            'coupon' => array(
                    'batch_generate' => '生成优惠码',
                    'receive' => '领取优惠码',
                    'use' => '使用优惠码',
                    'batch_delete' => '删除优惠码',
                    'setting' => '更新设置',
                ),
            'discount' => array(
                    'apply_audit' => '申请打折',
                    'audit_pass' => '审核通过',
                    'audit_reject' => '审核拒绝',
                    'start' => '开启',
                    'close' => '关闭',
                    'delete' => '删除',
                ),
            'money_card' => array(
                    'money_card_use' => '使用',
                    'export' => '导出',
                    'show_password' => '查询密码',
                    'batch_create' => '批量创建',
                    'lock' => '作废',
                    'unlock' => '启用',
                    'delete' => '删除',
                    'batch_lock' => '批量作废',
                    'batch_delete' => '批量删除',
                ),
            'question_plus' => array(
                    'update_setting' => '更新设置',
                ),
            'homework' => array(
                    'create' => '新增',
                    'update' => '修改',
                    'delete' => '删除',
                ),
            'exercise' => array(
                    'create' => '新增',
                    'update' => '修改',
                    'delete' => '删除',
                ),
        ), $result);
    }

    public function testSystemModuleConfig()
    {
        $result = Logger::systemModuleConfig();
        $this->assertEquals(array(
            'course' => array(
                    'create' => '创建课程',
                    'update' => '修改课程',
                    'update_picture' => '更新课程图片',
                    'publish' => '发布课程',
                    'close' => '关闭课程',
                    'delete' => '删除课程',
                    'add_task' => '添加学习任务',
                    'update_task' => '更新学习任务',
                    'delete_task' => '删除学习任务',
                    'add_student' => '添加课程学员',
                    'remove_student' => '移除课程学员',
                    'update_teacher' => '更新课程教师',
                    'cancel_teachers_all' => '取消用户的教师身份',
                    'delete_taskLearn' => '删除任务学习记录',
                    'delete_chapter' => '删除章节',
                    'delete_favorite' => '删除收藏',
                    'delete_note' => '删除笔记',
                    'delete_thread' => '删除话题',
                    'delete_review' => '删除评价',
                    'delete_announcement' => '删除公告',
                    'delete_status' => '删除动态',
                    'recommend' => '课程推荐',
                    'cancel_recommend' => '取消课程推荐',
                    'delete_material' => '移除资料',
                    'delete_testpaper' => '删除试卷',
                    'delete_question' => '删除题目',
                    'refresh_learning_progress' => '刷新学习进度',
                    'sync_when_task_create' => '同步创建任务',
                    'sync_when_task_update' => '同步更新任务',
                    'sync_when_task_delete' => '同步删除任务',
                    'clone_course_set' => '复制课程',
                ),
            'user' => array(
                    'exportCsv' => '导出',
                    'add' => '新增',
                    'edit' => '修改',
                    'send_email_verify' => '邮箱验证',
                    'nickname_change' => '修改昵称',
                    'password-changed' => '重置密码',
                    'pay-password-changed' => '充值支付密码',
                    'verifiedMobile-changed' => '重置手机',
                    'change_role' => '修改角色',
                    'unbind' => '解绑用户',
                    'login_success' => '登录成功',
                    'login_fail' => '登录失败',
                    'lock' => '禁封用户',
                    'unlock' => '解禁用户',
                    'recommend' => '推荐用户',
                    'cancel_recommend' => '取消推荐用户',
                    'approved' => '实名认证成功',
                    'approval_fail' => '实名认证失败',
                    'password-reset' => '重置密码',
                ),
            'system' => array(
                    'email_send_check' => '邮件自检',
                    'setting_email_change' => '更变邮箱',
                    'setting_email-verify' => '邮箱验证',
                    'update_settings' => '更新设置',
                    'questions_settings' => '题库设置',
                    'customerServiceSetting' => '客服管理',
                    'setting_userCenter' => '用户中心设置',
                    'update_block' => '更新编辑区',
                    'update_app_version' => '更新版本',
                ),
            'classroom' => array(
                    'create' => '新增班级',
                    'delete' => '删除班级',
                    'add_course' => '添加课程',
                    'add_student' => '添加学员',
                    'delete_course' => '移除课程',
                    'delete_review' => '删除评价',
                    'delete_thread' => '删除话题',
                    'update_picture' => '更新图片',
                    'remove_student' => '移除学员',
                    'recommend' => '推荐班级',
                    'cancel_recommend' => '取消推荐',
                ),
            'article' => array(
                    'update_settings' => '更新设置',
                    'create' => '新增',
                    'update' => '修改',
                    'update_property' => '更新属性',
                    'cancel_property' => '取消属性',
                    'trash' => '移动到回收站',
                    'removeThumb' => '移除图片',
                    'delete' => '删除',
                    'publish' => '发布',
                    'unpublish' => '取消发布',
                ),
            'notify' => array(
                    'create' => '创建',
                    'check_fail' => '检测',
                ),
            'order' => array(
                    'pay_result' => '支付结果',
                    'andit_refund' => '退款审核',
                    'refund_cancel' => '取消退款',
                    'unbind-back' => '解绑银行卡',
                ),
            'category' => array(
                    'create' => '新增',
                    'update' => '修改',
                    'delete' => '删除',
                ),
            'content' => array(
                    'create' => '新增',
                    'update' => '修改',
                    'trash' => '移动到回收站',
                    'delete' => '删除',
                    'publish' => '发布',
                ),
            'crontab' => array(
                    'job_start' => '开始任务',
                    'job_end' => '结束任务',
                ),
            'upload_file' => array(
                    'create' => '新增文件',
                    'delete' => '删除文件',
                    'download' => '下载文件',
                    'cloud_convert_callback' => '回调处理',
                    'cloud_convert_error' => '转码失败',
                ),
            'marker' => array(
                    'create' => '增加驻点',
                    'delete' => '删除驻点',
                    'mediaId_notExist' => '视频不存在',
                    'delete_question' => '删除驻点问题',
                ),
            'group' => array(
                    'create_thread' => '新增话题',
                    'delete_thread' => '删除话题',
                    'close_thread' => '关闭话题',
                    'open_thread' => '开启话题',
                ),
            'sms' => array(
                    'sms_forget_password' => '登录密码重置',
                    'sms_user_pay' => '使用网站余额支付',
                    'sms_forget_pay_password' => '支付密码重置',
                    'sms_bind' => '手机绑定',
                    'sms_classroom_publish' => '新班级发布',
                    'sms_course_publish' => '新课程发布',
                    'sms_normal_lesson_publish' => '学习任务发布通知（普通课程）',
                    'sms_live_lesson_publish' => '学习任务发布通知（直播）',
                    'sms_live_play_one_day' => '直播开播前通知（提前1天)',
                    'sms_live_play_one_hour' => '直播开播前通知（提前1小时）',
                    'sms_homework_check' => '作业完成批阅',
                    'sms_testpaper_check' => '试卷完成批阅',
                    'sms_course_buy_notify' => '课程购买',
                    'sms_classroom_buy_notify' => '班级购买',
                    'sms_vip_buy_notify' => '会员购买',
                    'sms_coin_buy_notify' => '虚拟币充值',
                ),
            'tag' => array(
                    'create' => '新增',
                    'update' => '修改',
                    'delete' => '删除',
                ),
            'coin' => array(
                    'update_settings' => '设置',
                    'add_coin' => '增加',
                    'deduct_coin' => '扣除',
                ),
            'announcement' => array(
                    'delete' => '删除公告',
                ),
            'open_course' => array(
                    'create_course' => '创建公开课',
                    'update_course' => '更新公开课',
                    'delete_course' => '删除课程',
                    'pulish_course' => '发布公开课',
                    'close_course' => '关闭公开课',
                    'update_picture' => '更新公开课图片',
                    'add_lesson' => '添加公开课课时',
                    'update_lesson' => '更新公开课课时',
                    'delete_lesson' => '删除课时',
                    'delete_member' => '删除学员',
                    'delete_material' => '移除资料',
                    'update_teacher' => '更新公开课教师',
                    'delete_recommend_course' => '删除推荐课程',
                ),
            'live' => array(
                'update_live_activity' => '更新直播活动',
                'generate_live_replay' => '生成录播回放',
                ),
        ), $result);
    }

    public function testGetModule()
    {
        $result = Logger::getModule(Logger::COURSE);

        $this->assertEquals(array(
                'create' => '创建课程',
                'update' => '修改课程',
                'update_picture' => '更新课程图片',
                'publish' => '发布课程',
                'close' => '关闭课程',
                'delete' => '删除课程',
                'add_task' => '添加学习任务',
                'update_task' => '更新学习任务',
                'delete_task' => '删除学习任务',
                'add_student' => '添加课程学员',
                'remove_student' => '移除课程学员',
                'update_teacher' => '更新课程教师',
                'cancel_teachers_all' => '取消用户的教师身份',
                'delete_taskLearn' => '删除任务学习记录',
                'delete_chapter' => '删除章节',
                'delete_favorite' => '删除收藏',
                'delete_note' => '删除笔记',
                'delete_thread' => '删除话题',
                'delete_review' => '删除评价',
                'delete_announcement' => '删除公告',
                'delete_status' => '删除动态',
                'recommend' => '课程推荐',
                'cancel_recommend' => '取消课程推荐',
                'delete_material' => '移除资料',
                'delete_testpaper' => '删除试卷',
                'delete_question' => '删除题目',
                'refresh_learning_progress' => '刷新学习进度',
                'sync_when_task_create' => '同步创建任务',
                'sync_when_task_update' => '同步更新任务',
                'sync_when_task_delete' => '同步删除任务',
                'clone_course_set' => '复制课程',
        ), $result);

        $result = Logger::getModule('empty');
        $this->assertEmpty($result);
    }
}
