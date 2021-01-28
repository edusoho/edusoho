<?php

namespace Biz\Common;

/**
 * logger的模块 以及操作.
 */
class Logger
{
    /**
     * [$COIN 虚拟币].
     *
     * @var string
     */
    const COIN = 'coin';
    /**
     * [$COUPON 优惠码].
     *
     * @var string
     */
    const COUPON = 'coupon';
    /**
     * [$DISCOUNT 打折活动].
     *
     * @var string
     */
    const DISCOUNT = 'discount';
    /**
     * [$EXERCISE 练习].
     *
     * @var [type]
     */
    const EXERCISE = 'exercise';
    /**
     * [$HOMEWORK 作业].
     *
     * @var string
     */
    const HOMEWORK = 'homework';
    /**
     * [$MONEY_CARD 学习卡].
     *
     * @var string
     */
    const MONEY_CARD = 'money_card';
    /**
     * [$QUESTIONPLUS 题库增强版].
     *
     * @var string
     */
    const QUESTIONPLUS = 'question_plus';
    /**
     * [$vip 会员].
     *
     * @var string
     */
    const VIP = 'vip';

    /**
     * [$course 课程].
     *
     * @var string
     */
    const COURSE = 'course';
    /**
     * [$GROUP 小组话题].
     *
     * @var string
     */
    const GROUP = 'group';
    /**
     * [$USER 用户].
     *
     * @var string
     */
    const USER = 'user';
    /**
     * [$ORDER 订单].
     *
     * @var string
     */
    const ORDER = 'order';
    /**
     * [$UPLOADFILE 文件].
     *
     * @var string
     */
    const UPLOADFILE = 'upload_file';
    /**
     * [$SYSTEM 系统设置].
     *
     * @var string
     */
    const SYSTEM = 'system';
    /**
     * [$classroom 班级].
     *
     * @var string
     */
    const CLASSROOM = 'classroom';
    /**
     * [$article 资讯].
     *
     * @var string
     */
    const ARTICLE = 'article';

    /**
     * [$NOTIFY 通知].
     *
     * @var string
     */
    const NOTIFY = 'notify';

    /**
     * [$CATEGORY 栏目].
     *
     * @var string
     */
    const CATEGORY = 'category';
    /**
     * [$CONTENT 资讯内容].
     *
     * @var string
     */
    const CONTENT = 'content';
    /**
     * [$CRONTAB 定时任务].
     *
     * @var string
     */
    const CRONTAB = 'crontab';

    /**
     * [$Marker 驻点].
     *
     * @var string
     */
    const MARKER = 'marker';
    /**
     * [$SMS sms].
     *
     * @var string
     */
    const SMS = 'sms';
    /**
     * [$tag 标签].
     *
     * @var string
     */
    const TAG = 'tag';
    /**
     * [$announcement 公告].
     *
     * @var string
     */
    const ANNOUNCEMENT = 'announcement';

    /**
     * [$open_course 公开课].
     *
     * @var string
     */
    const OPEN_COURSE = 'open_course';

    /**
     * [$live 直播].
     *
     * @var string
     */
    const LIVE = 'live';

    const ACTION_REFRESH_LEARNING_PROGRESS = 'refresh_learning_progress';

    const ACTION_SYNC_WHEN_TASK_CREATE = 'sync_when_task_create';

    const ACTION_SYNC_WHEN_TASK_UPDATE = 'sync_when_task_update';

    const ACTION_SYNC_WHEN_TASK_DELETE = 'sync_when_task_delete';

    const ACTION_CLONE_COURSE_SET = 'clone_course_set';

    public static function getModule($module)
    {
        $modules = array_merge(array_keys(self::systemModuleConfig()), array_keys(self::pluginModuleConfig()));

        if (in_array($module, $modules)) {
            $allModules = array_merge(self::systemModuleConfig(), self::pluginModuleConfig());

            return $allModules[$module];
        }

        return [];
    }

    /**
     * 模块(module)  -> 操作(action).
     */
    public static function systemModuleConfig()
    {
        return [
            self::COURSE => [
                'create' => '创建课程',
                'update' => '修改课程',
                'update_picture' => '更新课程图片',
                'publish' => '发布课程',
                'close' => '关闭课程',
                'delete' => '删除课程',
                'add_task' => '添加学习任务',
                'publish_lesson' => '发布学习任务',
                'unpublish_lesson' => '关闭学习任务',
                'update_task' => '更新学习任务',
                'delete_task' => '删除学习任务',
                'add_student' => '添加课程学员',
                'remove_student' => '移除课程学员',
                'update_teacher' => '更新课程教师',
                'cancel_teachers_all' => '取消用户的教师身份',
                'delete_taskLearn' => '删除任务学习记录',
                'delete_chapter' => '删除章节',
                'delete_lesson' => '删除课时',
                'delete_favorite' => '删除收藏',
                'delete_note' => '删除笔记',
                'delete_thread' => '删除话题',
                'delete_review' => '删除评价',
                'delete_announcement' => '删除公告',
                'delete_status' => '删除动态',
                'recommend' => '课程推荐',
                'cancel_recommend' => '取消课程推荐',
                'delete_material' => '移除资料',
                //'add_testpaper' => '新增试卷',
                //'publish_testpaper' => '发布试卷',
                //'close_testpaper' => '关闭试卷',
                'delete_testpaper' => '删除试卷',
                //'add_question' => '新增题目',
                'delete_question' => '删除题目',
                self::ACTION_REFRESH_LEARNING_PROGRESS => '刷新学习进度',
                self::ACTION_SYNC_WHEN_TASK_CREATE => '同步创建任务',
                self::ACTION_SYNC_WHEN_TASK_UPDATE => '同步更新任务',
                self::ACTION_SYNC_WHEN_TASK_DELETE => '同步删除任务',
                self::ACTION_CLONE_COURSE_SET => '复制课程',
            ],

            self::USER => [
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
                'password-reset' => '重置密码', ],
            self::SYSTEM => [
                'email_send_check' => '邮件自检',
                'setting_email_change' => '更变邮箱',
                'setting_email-verify' => '邮箱验证',
                'update_settings' => '更新设置',
                'questions_settings' => '题库设置',
                'customerServiceSetting' => '客服管理',
                'setting_userCenter' => '用户中心设置',
                'update_block' => '更新编辑区',
                'update_app_version' => '更新版本', ],
            self::CLASSROOM => [
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
            ],
            self::ARTICLE => [
                'update_settings' => '更新设置',
                'create' => '新增',
                'update' => '修改',
                'update_property' => '更新属性',
                'cancel_property' => '取消属性',
                'trash' => '移动到回收站',
                'removeThumb' => '移除图片',
                'delete' => '删除',
                'publish' => '发布',
                'unpublish' => '取消发布', ],

            self::NOTIFY => [
                'create' => '创建',
                'check_fail' => '检测', ],
            self::ORDER => [
                'pay_result' => '支付结果',
                'andit_refund' => '退款审核',
                'refund_cancel' => '取消退款',
                'unbind-back' => '解绑银行卡', ],
            self::CATEGORY => [
                'create' => '新增',
                'update' => '修改',
                'delete' => '删除', ],
            self::CONTENT => [
                'create' => '新增',
                'update' => '修改',
                'trash' => '移动到回收站',
                'delete' => '删除',
                'publish' => '发布', ],

            self::CRONTAB => [
                'job_start' => '开始任务',
                'job_end' => '结束任务', ],
            self::UPLOADFILE => [
                'create' => '新增文件',
                'delete' => '删除文件',
                'download' => '下载文件',
                'cloud_convert_callback' => '回调处理',
                'cloud_convert_error' => '转码失败', ],
            self::MARKER => [
                'create' => '增加驻点',
                'delete' => '删除驻点',
                'mediaId_notExist' => '视频不存在',
                'delete_question' => '删除驻点问题', ],
            self::GROUP => [
                'create_thread' => '新增话题',
                'delete_thread' => '删除话题',
                'close_thread' => '关闭话题',
                'open_thread' => '开启话题',
            ],
            self::SMS => [
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
            ],
            self::TAG => [
                'create' => '新增',
                'update' => '修改',
                'delete' => '删除', ],
            self::COIN => [
                'update_settings' => '设置',
                'add_coin' => '增加',
                'deduct_coin' => '扣除', ],
            self::ANNOUNCEMENT => [
                'delete' => '删除公告',
            ],
            self::OPEN_COURSE => [
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
                //'add_material' => '新增资料',
                'delete_material' => '移除资料',
                'update_teacher' => '更新公开课教师',
                'delete_recommend_course' => '删除推荐课程',
            ],
            self::LIVE => [
                'update_live_activity' => '更新直播活动',
                'generate_live_replay' => '生成录播回放',
            ],
        ];
    }

    public static function pluginModuleConfig()
    {
        return [
            self::VIP => [
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
                'update_setting' => '更新设置', ],
            self::COUPON => [
                'batch_generate' => '生成优惠码',
                'receive' => '领取优惠码',
                'use' => '使用优惠码',
                'batch_delete' => '删除优惠码',
                'setting' => '更新设置', ],
            self::DISCOUNT => [
                'apply_audit' => '申请打折',
                'audit_pass' => '审核通过',
                'audit_reject' => '审核拒绝',
                'start' => '开启',
                'close' => '关闭',
                'delete' => '删除', ],
            self::MONEY_CARD => [
                'money_card_use' => '使用',
                'export' => '导出',
                'show_password' => '查询密码',
                'batch_create' => '批量创建',
                'lock' => '作废',
                'unlock' => '启用',
                'delete' => '删除',
                'batch_lock' => '批量作废',
                'batch_delete' => '批量删除', ],
            self::QUESTIONPLUS => [
                'update_setting' => '更新设置', ],
            self::HOMEWORK => [
                'create' => '新增',
                'update' => '修改',
                'delete' => '删除', ],
            self::EXERCISE => [
                'create' => '新增',
                'update' => '修改',
                'delete' => '删除', ],
        ];
    }

    public static function getLogModuleDict()
    {
        return [
            self::USER => '用户',
            self::COURSE => '课程',
            self::CLASSROOM => '班级',
            self::GROUP => '小组话题',
            self::UPLOADFILE => '文件',
            self::ORDER => '订单',
            self::ARTICLE => '资讯',
            self::CATEGORY => '栏目',
            self::CONTENT => '资讯内容',
            self::NOTIFY => '通知',
            self::SMS => '短信',
            self::TAG => '标签',
            self::SYSTEM => '系统设置',
            self::CRONTAB => '定时任务',
            self::MARKER => '驻点',
            self::VIP => '会员',
            self::COIN => '虚拟币',
            self::COUPON => '优惠码',
            self::MONEY_CARD => '学习卡',
            self::DISCOUNT => '打折活动',
            self::EXERCISE => '练习',
            self::HOMEWORK => '作业',
            self::QUESTIONPLUS => '题库增强版',
            self::ANNOUNCEMENT => '公告',
            self::OPEN_COURSE => '公开课',
            self::LIVE => '直播',
        ];
    }
}
