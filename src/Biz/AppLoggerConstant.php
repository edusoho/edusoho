<?php

namespace Biz;

class AppLoggerConstant implements LoggerConstantInterface
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
    const QUESTION_PLUS = 'question_plus';
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
    const UPLOAD_FILE = 'upload_file';
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
     * [$LIVE 直播].
     *
     * @var string
     */
    const LIVE = 'live';

    public function getActions()
    {
        return array(
            self::COURSE => array(
                'create',
                'update',
                'update_picture',
                'publish',
                'close',
                'delete',
                'add_task',
                'update_task',
                'delete_task',
                'add_student',
                'remove_student',
                'update_teacher',
                'cancel_teachers_all',
                'delete_taskLearn',
                'delete_chapter',
                'delete_favorite',
                'delete_note',
                'delete_thread',
                'delete_review',
                'delete_announcement',
                'delete_status',
                'recommend',
                'cancel_recommend',
                'delete_material',
                //'add_testpaper',
                //'publish_testpaper',
                //'close_testpaper',
                'delete_testpaper',
                //'add_question',
                'delete_question',
                'refresh_learning_progress',
                'sync_when_task_create',
                'sync_when_task_update',
                'sync_when_task_delete',
                'clone_course_set',
                'unlock_course',
            ),

            self::USER => array(
                'exportCsv',
                'add',
                'edit',
                'send_email_verify',
                'nickname_change',
                'password-changed',
                'pay-password-changed',
                'verifiedMobile-changed',
                'change_role',
                'unbind',
                'login_success',
                'login_fail',
                'lock',
                'unlock',
                'recommend',
                'cancel_recommend',
                'approved',
                'approval_fail',
                'password-reset',
            ),
            self::SYSTEM => array(
                'email_send_check',
                'setting_email_change',
                'setting_email-verify',
                'update_settings',
                'questions_settings',
                'customerServiceSetting',
                'setting_userCenter',
                'update_block',
                'update_app_version',
            ),
            self::CLASSROOM => array(
                'create',
                'delete',
                'add_course',
                'add_student',
                'delete_course',
                'delete_review',
                'delete_thread',
                'update_picture',
                'remove_student',
                'recommend',
                'cancel_recommend',
            ),
            self::ARTICLE => array(
                'update_settings',
                'create',
                'update',
                'update_property',
                'cancel_property',
                'trash',
                'removeThumb',
                'delete',
                'publish',
                'unpublish',
            ),

            self::NOTIFY => array(
                'create',
                'check_fail',
            ),
            self::ORDER => array(
                'pay_result',
                'andit_refund',
                'refund_cancel',
                'unbind-back',
                'course_callback',
                'classroom_callback',
                'adjust_price',
            ),
            self::CATEGORY => array(
                'create',
                'update',
                'delete',
            ),
            self::CONTENT => array(
                'create',
                'update',
                'trash',
                'delete',
                'publish',
            ),

            self::CRONTAB => array(
                'job_start',
                'job_end',
            ),
            self::UPLOAD_FILE => array(
                'create',
                'delete',
                'download',
                'cloud_convert_callback',
                'cloud_convert_error',
            ),
            self::MARKER => array(
                'create',
                'delete',
                'mediaId_notExist',
                'delete_question',
            ),
            self::GROUP => array(
                'create_thread',
                'delete_thread',
                'close_thread',
                'open_thread',
            ),
            self::SMS => array(
                'sms_forget_password',
                'sms_user_pay',
                'sms_forget_pay_password',
                'sms_bind',
                'sms_classroom_publish',
                'sms_course_publish',
                'sms_normal_lesson_publish',
                'sms_live_lesson_publish',
                'sms_live_play_one_day',
                'sms_live_play_one_hour',
                'sms_homework_check',
                'sms_testpaper_check',
                'sms_course_buy_notify',
                'sms_classroom_buy_notify',
                'sms_vip_buy_notify',
                'sms_coin_buy_notify',
            ),
            self::TAG => array(
                'create',
                'update',
                'delete',
            ),
            self::COIN => array(
                'update_settings',
                'add_coin',
                'deduct_coin',
            ),
            self::ANNOUNCEMENT => array(
                'delete',
            ),
            self::OPEN_COURSE => array(
                'create_course',
                'update_course',
                'delete_course',
                'pulish_course',
                'close_course',
                'update_picture',
                'add_lesson',
                'update_lesson',
                'delete_lesson',
                'delete_member',
                //'add_material' => '新增资料',
                'delete_material',
                'update_teacher',
                'delete_recommend_course',
            ),
            self::LIVE => array(
                'update_live_activity',
                'generate_live_replay',
            ),
        );
    }

    public function getModules()
    {
        return array(
            self::SYSTEM,
            self::COURSE,
            self::USER,
            self::ORDER,
            self::CLASSROOM,
            self::GROUP,
            self::SMS,
            self::MARKER,
            self::UPLOAD_FILE,
            self::CATEGORY,
            self::TAG,
            self::ARTICLE,
            self::CONTENT,
            self::ANNOUNCEMENT,
            self::NOTIFY,
            self::CRONTAB,
            self::LIVE,
//            self::COIN,
//            self::COUPON,
//            self::DISCOUNT,
//            self::MONEY_CARD,
//            self::QUESTION_PLUS,
//            self::VIP,
        );
    }
}
