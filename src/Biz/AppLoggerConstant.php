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
     * [$USER 用户].
     *
     * @var string
     */
    const ROLE = 'role';
    /**
     * [$ROLE 身份].
     *
     * @var string
     */
    const THREAD = 'thread';
    /**
     * [@THREAD 话题].
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
    const TAG_GROUP = 'tagGroup';
    /**
     * [@TAG_GROUP 标签组].
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

    /**
     * [$MOBILE 手机客户端]
     *
     * @var string
     */
    const MOBILE = 'mobile';

    /**
     *  [$PUSH 推送]
     *
     * $var string
     */
    const PUSH = 'push';

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
                'task_set_optional',
                'task_unset_optional',
                'create_lesson',
                'delete_lesson',
                'lesson_set_optional',
                'lesson_unset_optional',
                'add_student',
                'remove_student',
                'update_teacher',
                'cancel_teachers_all',
                'delete_taskLearn',
                'create_chapter',
                'delete_chapter',
                'delete_favorite',
                'delete_note',
                'create_thread',
                'update_thread',
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
                'create_course',
                'update_course',
                'delete_course',
                'publish_course',
                'close_course',
                'update_draft',
                'join_course',
            ),

            self::PUSH => array(
                'course_thread_create',
                'course_thread_post_create',
            ),

            self::USER => array(
                'exportCsv',
                'add',
                'update',
                'send_email_verify',
                'nickname_change',
                'password-changed',
                'pay-password-changed',
                'password-security-answers',
                'verifiedMobile-changed',
                'email-changed',
                'avatar-changed',
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
            self::ROLE => array(
                'create',
                'update',
                'delete',
            ),
            self::THREAD => array(
                'create',
                'update',
                'delete',
            ),
            self::SYSTEM => array(
                'email_send_check',
                'setting_email_change',
                'setting_email-verify',
                'questions_settings',
                'customerServiceSetting',
                'update_block',
                'update_app_version',
                'update_settings.site',
                'update_settings.theme',
                'update_settings.mailer',
                'update_settings.cloud_email_crm',
                'update_settings.consult',
                'update_settings.esBar',
                'update_settings.default',
                'update_settings.security',
                'update_settings.login_bind',
                'update_settings.user_partner',
                'update_settings.auth',
                'update_settings.course',
                'update_settings.message',
                'update_settings.course_default',
                'update_settings.questions',
                'update_settings.classroom',
                'update_settings.article',
                'update_settings.group',
                'update_settings.invite',
                'update_settings.payment',
                'update_settings.coin',
                'update_settings.refund',
                'update_settings.blacklist_ip',
                'update_settings.post_num_rules',
                'update_settings.cloud_consult',
                'update_settings.storage',
                'update_settings.live-course',
                'update_settings.cloud_sms',
                'update_settings.cloud_search',
                'update_settings.app_im',
                'update_settings.cloud_attachment',
                'update_settings.xapi',
                'update_settings.mobile',
                'update_settings.wap',
            ),
            self::CLASSROOM => array(
                'create',
                'update',
                'delete',
                'add_course',
                'add_student',
                'delete_course',
                'delete_review',
                'update_picture',
                'remove_student',
                'recommend',
                'cancel_recommend',
                'publish',
                'close',
                'update_head_teacher',
                'join_classroom',
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
                'wechat_notify_lesson_publish',
                'wechat_notify_live_play',
                'wechat_notify_exam_result',
                'wechat_notify_homework_result',
                'wechat_notify_pay_success',
                'wechat_notify_coin_recharge',
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
                'update_thread',
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
            self::TAG_GROUP => array(
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
                'create',
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
                'delete_live_activity',
            ),
            self::MOBILE => array(
                'face_login',
            ),
        );
    }

    public function getModules()
    {
        return array(
            self::SYSTEM,
            self::COURSE,
            self::USER,
            self::ROLE,
            self::THREAD,
            self::ORDER,
            self::CLASSROOM,
            self::GROUP,
            self::SMS,
            self::MARKER,
            self::UPLOAD_FILE,
            self::CATEGORY,
            self::TAG,
            self::TAG_GROUP,
            self::ARTICLE,
            self::CONTENT,
            self::ANNOUNCEMENT,
            self::NOTIFY,
            self::CRONTAB,
            self::LIVE,
            self::MOBILE,
            self::PUSH,
//            self::COIN,
//            self::COUPON,
//            self::DISCOUNT,
//            self::MONEY_CARD,
//            self::QUESTION_PLUS,
//            self::VIP,
        );
    }
}
