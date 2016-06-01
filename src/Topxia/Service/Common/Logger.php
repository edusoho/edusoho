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
    const UPLOADFILE = 'uploadFile';
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
    const tag = 'tag';

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
            'coin'          => array(),
            'coupon'        => array(),
            'discount'      => array(),
            'exercise'      => array(),
            'homework'      => array(),
            'money_card'    => array(),
            'question_plus' => array(),
            'vip'           => array(),
            'system'        => array('update_settings'),
            'classroom'     => array('add_student'),
            'article'       => array(),
            'user'          => array('add'),
            'notify'        => array(),
            'order'         => array(),
            'category'      => array('create'),
            'content'       => array(),
            'course'        => array('course'),
            'crontab'       => array('job_start', 'job_end'),
            'uploadFile'    => array('delete'),
            'marker'        => array(),
            'thread'        => array('delete')

        );
    }
}
