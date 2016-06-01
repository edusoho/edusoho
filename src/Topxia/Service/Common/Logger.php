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
     * [$USERIMPORTER 用户导出]
     * @var string
     */
    const USERIMPORTER = 'userimporter';
    /**
     * [$vip 会员]
     * @var string
     */
    const vip = 'vip';
    /**
     * [$MEMBER VIP会员] 重构
     * @var string
     */
    const MEMBER = 'Member';
    /**
     * [$vipimporter 会员导出] 重构
     * @var string
     */
    const vipimporter = 'vipimporter';
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
     * [$CLASSROOMREVIEW 班级评价]
     * @var string
     */
    const CLASSROOMREVIEW = 'classroom_review';
    /**
     * [$CASH 虚拟币]
     * @var string
     */
    const CASH = 'cash';
    /**
     * [$article 资讯]
     * @var string
     */
    const ARTICLE = 'article';
    /**
     * [$article 资讯] 重构
     * @var string
     */
    const article = 'Article';

    const setArticleProperty    = 'setArticleProperty';
    const cancelArticleProperty = 'cancelArticleProperty';

    /**
     * [$USER 用户]
     * @var string
     */
    const USER = 'user';
    /**
     * [$NOTIFY 通知]
     * @var string
     */
    const NOTIFY = 'notify';
    /**
     * [$ORDER 订单]
     * @var string
     */
    const ORDER = 'order';
    /**
     * [$CATEGORY 栏目]
     * @var string
     */
    const CATEGORY = 'category';
    /**
     * [$BLOCK 编辑区]
     * @var string
     */
    const BLOCK = 'block';
    /**
     * [$CONTENT 资讯内容]
     * @var string
     */
    const CONTENT = 'content';
    /**
     * [$QUESTION 课程问题]
     * @var string
     */
    const QUESTION = 'question';
    /**
     * [$TESTPAPER 课程试卷]
     * @var string
     */
    const TESTPAPER = 'testpaper';
    /**
     * [$MATERIAL 课程资料]
     * @var string
     */
    const MATERIAL = 'material';
    /**
     * [$chapter 课时章/节] 重构
     * @var string
     */
    const CHAPTER = 'chapter';
    /**
     * [$DRAFT 课程草稿]重构
     * @var string
     */
    const DRAFT = 'draft';
    /**
     * [$lesson 课时]重构
     * @var string
     */
    const LESSON = 'lesson';
    /**
     * [$LESSONLEARN 课时时长]重构
     * @var string
     */
    const LESSONLEARN = 'lessonLearn';
    /**
     * [$LESSONREPLAY 课程录播]重构
     * @var string
     */
    const LESSONREPLAY = 'LessonReplay';
    /**
     * [$LESSONVIEW 课程播放时长]重构
     * @var string
     */
    const LESSONVIEW = 'lessonView';
    /**
     * [$FAVORITE 课程收藏] 重构
     * @var string
     */
    const FAVORITE = 'favorite';
    /**
     * [$note 课程笔记]
     * @var string
     */
    const NOTE = 'note';
    /**
     * [$thread 课程话题]
     * @var string
     */
    const THREAD = 'thread';
    /**
     * [$review 课程评价]
     * @var string
     */
    const REVIEW = 'review';
    /**
     * [$announcement 课程公告]
     * @var string
     */
    const ANNOUNCEMENT = 'announcement';
    /**
     * [$STATUS 课程动态]
     * @var string
     */
    const STATUS = 'status';
    /**
     * [$member 课程学员]
     * @var string
     */
    const member = 'member';
    /**
     * [$course 课程]
     * @var string
     */
    const COURSE = 'course';
    /**
     * [$CRONTAB 定时任务]
     * @var string
     */
    const CRONTAB = 'crontab';
    /**
     * [$UPLOADFILE 文件]
     * @var string
     */
    const UPLOADFILE = 'upload_file';
//重构
    const uploadfile = 'uploadfile';
    /**
     * [$Marker 驻点]
     * @var string
     */
    const MARKER = 'Marker';
    /**
     * [$questionMarker 驻点问题]
     * @var string
     */
    const QUESTIONMARKER = 'questionMarker';

    /**
     * [$course_order 订单]
     * @var string
     */
    const COURSE_ORDER = 'course_order';
    /**
     * [$SMS sms]
     * @var string
     */
    const SMS = 'sms';
    /**
     * [$tag 标签]
     * @var string
     */
    const tag = 'tag';

    /**
     * [$thread 小组话题]
     * @var string
     */
    const thread = 'thread';

//重构
    /**
     * [$setting 设置]
     * @var string
     */
    const SETTING = 'setting';

    public static function getModule($module)
    {
        $modules = array_keys(self::moduleConfig());

        if (in_array($module, $modules)) {
            return $module;
        }

        throw new NotFoundException("模块名不存在,请检查是否拼写错误");
    }

    /**
     * 模块(module)  -> 操作(action)
     * @return [type] [description]
     */
    public static function moduleConfig()
    {
        return array(
            'coin'             => array(),
            'coupon'           => array(),
            'discount'         => array(),
            'exercise'         => array(),
            'homework'         => array(),
            'money_card'       => array(),
            'question_plus'    => array(),
            'userimporter'     => array(),
            'level'            => array(),
            'vip'              => array(),
            'Member'           => array('delete'),
            'vipimporter'      => array('exportCsv'),
            'system'           => array('update_settings'),
            'classroom'        => array('add_student'),
            'classroom_review' => array('delete'),
            'cash'             => array(),
            'article'          => array(),
            'Article'          => array('update'),
            'user'             => array('add'),
            'notify'           => array(),
            'order'            => array(),
            'category'         => array('create'),
            'block'            => array('update'),
            'content'          => array(),
            'question'         => array(),
            'testpaper'        => array('delete'),
            'material'         => array('delete'),
            'chapter'          => array('delete'),
            'draft'            => array('delete'),
            'lesson'           => array('delete'),
            'lessonLearn'      => array('delete'),
            'LessonReplay'     => array('delete'),
            'lessonView'       => array('delete'),
            'favorite'         => array('delete'),
            'note'             => array('delete'),
            'thread'           => array('delete'),
            'review'           => array('delete'),
            'announcement'     => array('delete'),
            'status'           => array('delete'),
            'member'           => array('delete'),
            'course'           => array('course'),
            'crontab'          => array('job_start', 'job_end'),
            'upload_file'      => array('delete'),
            'Marker'           => array(),
            'questionMarker'   => array(),
            'money_card_batch' => array()

        );
    }
}
