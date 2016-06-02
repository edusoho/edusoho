<?php
namespace Topxia\Service\Common;

/**
 * logger的模块 以及操作
 */
class Logger
{
    /**
     * [$COIN 虚拟币]
     * @var string
     */
    const COIN = 'coin';
    /**
     * [$COUPON 优惠码]
     * @var string
     */
    const COUPON = 'coupon';
    /**
     * [$DISCOUNT 打折活动]
     * @var string
     */
    const DISCOUNT = 'discount';
    /**
     * [$EXERCISE 练习]
     * @var [type]
     */
    const EXERCISE = 'exercise';
    /**
     * [$HOMEWORK 作业]
     * @var string
     */
    const homework = 'homework';
    /**
     * [$MONEY_CARD 学习卡]
     * @var string
     */
    const MONEY_CARD = 'money_card';
    /**
     * [$QUESTIONPLUS 题库增强版]
     * @var string
     */
    const QUESTIONPLUS = 'question_plus';
    /**
     * [$vip 会员]
     * @var string
     */
    const vip = 'vip';

    /**
     * [$course 课程]
     * @var string
     */
    const COURSE = 'course';
    /**
     * [$thread 小组话题]
     * @var string
     */
    const THREAD = 'thread';
    /**
     * [$USER 用户]
     * @var string
     */
    const USER = 'user';
    /**
     * [$ORDER 订单]
     * @var string
     */
    const ORDER = 'order';
    /**
     * [$UPLOADFILE 文件]
     * @var string
     */
    const UPLOADFILE = 'uploadfile';
    /**
     * [$SYSTEM 系统设置]
     * @var string
     */
    const SYSTEM = 'system';
    /**
     * [$classroom 班级]
     * @var string
     */
    const CLASSROOM = 'classroom';
    /**
     * [$article 资讯]
     * @var string
     */
    const ARTICLE = 'article';

    /**
     * [$NOTIFY 通知]
     * @var string
     */
    const NOTIFY = 'notify';

    /**
     * [$CATEGORY 栏目]
     * @var string
     */
    const CATEGORY = 'category';
    /**
     * [$CONTENT 资讯内容]
     * @var string
     */
    const CONTENT = 'content';
    /**
     * [$CRONTAB 定时任务]
     * @var string
     */
    const CRONTAB = 'crontab';

    /**
     * [$Marker 驻点]
     * @var string
     */
    const MARKER = 'marker';
    /**
     * [$SMS sms]
     * @var string
     */
    const SMS = 'sms';
    /**
     * [$tag 标签]
     * @var string
     */
    const TAG = 'tag';

    public static function getModule($module)
    {
        $modules = array_merge(array_keys(self::systemModuleConfig()), array_keys(self::pluginModuleConfig()));

        if (in_array($module, $modules)) {
            return $module;
        }
        return $module;
        //  throw new NotFoundException("模块名不存在,请检查是否拼写错误");
    }

    /**
     * 模块(module)  -> 操作(action)
     * 操作待完善
     * @return [type] [description]
     */
    public static function systemModuleConfig()
    {
        return array(
            'system'      => array('email_send_check' => '邮件自检', 'setting_email_change' => '更变邮箱', 'setting_email-verify' => '邮箱验证', 'update_settings' => '更新设置', 'questions_settings' => '题库设置', 'customerServiceSetting' => '客服管理', 'setting_userCenter' => '用户中心设置', 'update_block' => '更新编辑区', 'update_app_version' => '更新版本'),
            'classroom'   => array('add_student' => '添加学员', 'delete_review' => '删除评价', 'delete' => '删除班级', 'update_picture' => '更新图片', 'remove_student' => '移除学员', 'recommend' => '推荐班级', 'cancel_recommend' => '取消推荐'),
            'article'     => array('update_settings' => '更新设置', 'create' => '新增', 'update' => '修改', 'update_property' => '更新属性', 'cancel_property' => '取消属性', 'trash' => '移动到回收站', 'removeThumb' => '移除图片', 'delete' => '删除', 'publish' => '发布', 'unpublish' => '取消发布'),
            'user'        => array('exportCsv' => '导出', 'add' => '新增', 'edit' => '修改', 'send_email_verify' => '邮箱验证', 'nickname_change' => '修改昵称', 'password-changed' => '重置密码', 'pay-password-changed' => '充值支付密码', 'verifiedMobile-changed' => '重置手机', 'change_role' => '修改角色', 'unbind' => '解绑用户', 'login_success' => '登录成功', 'login_fail' => '登录失败', 'lock' => '禁封用户', 'unlock' => '解禁用户', 'recommend' => '推荐用户', 'cancel_recommend' => '取消推荐用户', 'approved' => '实名认证成功', 'approval_fail' => '实名认证失败', 'password-reset' => '重置密码'),
            'notify'      => array('create' => '创建', 'check_fail' => '检测'),
            'order'       => array('pay_result' => '支付结果', 'andit_refund' => '退款审核', 'refund_cancel' => '取消退款', 'unbind-back' => '解绑银行卡'),
            'category'    => array('create' => '新增', 'update' => '修改', 'delete' => '删除'),
            'content'     => array('create' => '增加', 'update' => '修改', 'trash' => '移动到回收站', 'delete' => '删除', 'publish' => '发布'),
            'course'      => array('delete_testpaper' => '删除试卷', 'delete_material' => '删除课时资料', 'delete_chapter' => '删除章节', 'delete_draft' => '删除草稿', 'delete_lesson' => '删除课时', 'delete_lessonLearn' => '删除课时学习记录', 'delete_lessonReplay' => '删除课时播放记录', 'delete_lessonView' => '删除课时浏览记录', 'delete_favorite' => '删除收藏', 'delete_note' => '删除笔记', 'delete_thread' => '删除话题', 'delete_review' => '删除评价', 'delete_announcement' => '删除公告', 'delete_status' => '删除动态', 'delete_member' => '删除学员', 'delete' => '删除课程', 'add_student' => '增加学员', 'create' => '增加课程', 'update' => '修改课程', 'update_picture' => '更新图片', 'recommend' => '课程推荐', 'cancel_recommend' => '取消推荐', 'publish' => '发布课程', 'close' => '关闭课程', 'add_lesson' => '增加课时', 'update_draft' => '更新草稿', 'update_lesson' => '更新课时', 'update_teacher' => '更新教师', 'cancel_teachers_all' => '取消所有教师角色', 'remove_student' => '移除学员'),
            'crontab'     => array('job_start' => '开始任务', 'job_end' => '结束任务'),
            'upload_file' => array('delete' => '删除文件', 'download' => '下载文件', 'cloud_convert_callback' => '回调处理', 'cloud_convert_error' => '转码失败'),
            'marker'      => array('mediaId_notExist' => '视频不存在', 'delete' => '删除驻点', 'delete_question' => '删除驻点问题'),
            'thread'      => array('delete' => '删除'),
            'sms'         => array(),
            'tag'         => array('create' => '新增', 'update' => '修改', 'delete' => '删除'),
            'coin'        => array('update_settings' => '设置', 'add_coin' => '增加', 'deduct_coin' => '扣除')
        );
    }

    public static function pluginModuleConfig()
    {
        return array(
            'vip'           => array('create_level' => '添加等级', 'update_level' => '修改等级', 'on_level' => '开启加入', 'off_level' => '关闭加入', 'delete_level' => '删除等级', 'edit' => '修改', 'delete_member' => '删除会员', 'exportCsv' => '导出', 'update_setting' => '更新设置'),
            'coupon'        => array('coupon_setting' => '更新设置', 'batch_delete' => '批量删除'),
            'discount'      => array('apply_audit' => '申请打折', 'start' => '开启', 'close' => '关闭', 'delete' => '删除'),
            'money_card'    => array('money_card_use' => '使用', 'export' => '导出', 'show_password' => '查询密码', 'batch_create' => '批量创建', 'lock' => '作废', 'unlock' => '启用', 'delete' => '删除', 'batch_lock' => '批量作废', 'batch_delete' => '批量删除'),
            'question_plus' => array('update_setting' => '更新设置'),
            'homework'      => array('create' => '新增', 'update' => '修改', 'delete' => '删除'),
            'exercise'      => array('create' => '新增', 'update' => '修改', 'delete' => '删除')
        );
    }

    public static function getLogModuleDict()
    {
        return array(
            'user'          => '用户',
            'course'        => '课程',
            'classroom'     => '班级',
            'thread'        => '小组话题',
            'upload_file'   => '文件',
            'order'         => '订单',
            'article'       => '资讯',
            'category'      => '栏目',
            'content'       => '资讯内容',
            'notify'        => '通知',
            'sms'           => '短信',
            'tag'           => '标签',
            'system'        => '系统设置',
            'crontab'       => '定时任务',
            'marker'        => '驻点',
            'vip'           => '会员',
            'coin'          => '虚拟币',
            'coupon'        => '优惠码',
            'money_card'    => '学习卡',
            'discount'      => '打折活动',
            'exercise'      => '练习',
            'homework'      => '作业',
            'question_plus' => '题库增强版'
        );
    }
}
