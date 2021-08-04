<?php

namespace Biz\System\SettingModule;

use Biz\System\SettingNames;

class CourseSetting extends AbstractSetting
{
    const defaultCourseSetting = [
        'welcome_message_enabled' => '0',
        'welcome_message_body' => '{{nickname}},欢迎加入课程{{course}}',
        'teacher_manage_marketing' => '0',
        'teacher_search_order' => '0',
        'teacher_manage_student' => '0',
        'teacher_export_student' => '0',
        'explore_default_orderBy' => 'latest',
        'free_course_nologin_view' => '1',
        'relatedCourses' => '0',
        'coursesPrice' => '0',
        'allowAnonymousPreview' => '1',
        'copy_enabled' => '0',
        'testpaperCopy_enabled' => '0',
        'custom_chapter_enabled' => '0',
        'show_cover_num_mode' => 'studentNum',
        'show_review' => '1',
    ];

    const defaultLiveCourseSetting = [
        'live_course_enabled' => '0',
        'live_student_capacity' => '0',
    ];

    const defaultVideoMediaSetting = [
        'statistical_dimension' => 'page',
        'play_rule' => 'no_action',
        'play_continuously' => 'off',
    ];

    const defaultMultipleLearnSetting = [
        'multiple_learn_enable' => '1',
        'multiple_learn_kick_mode' => 'kick_previous',
    ];

    protected $allowSettingNames = [
        SettingNames::COURSE_SETTING,
        SettingNames::LIVE_COURSE_SETTING,
        SettingNames::VIDEO_LEARN_SETTING,
        SettingNames::TASK_MULTIPLE_SETTING,
    ];

    public function getCourseSetting()
    {
        $courseSetting = $this->get(SettingNames::COURSE_SETTING, self::defaultCourseSetting);
        $liveCourseSetting = $this->get(SettingNames::LIVE_COURSE_SETTING, self::defaultLiveCourseSetting);

        return [
            'welcome_message_enabled' => !empty($courseSetting['welcome_message_enabled']),
            'welcome_message_body' => $courseSetting['welcome_message_body'],
            'teacher_manage_marketing' => $courseSetting['teacher_manage_marketing'],
            'teacher_search_order' => $courseSetting['teacher_search_order'],
            'teacher_manage_student' => $courseSetting['teacher_manage_student'],
            'teacher_export_student' => $courseSetting['teacher_export_student'],
        ];
    }

    /**
     * @return array 课程任务学习设置
     */
    public function getCourseTaskLearnConfig()
    {
        $videoConfig = $this->get(SettingNames::VIDEO_LEARN_SETTING, self::defaultVideoMediaSetting);
        $multipleLearnSetting = $this->get(SettingNames::TASK_MULTIPLE_SETTING, self::defaultMultipleLearnSetting);

        return [
            'non_focus_learning_video_play_rule' => $videoConfig['play_rule'], //非专注学习播放规则：auto_pause（自动暂停）, no_action（不作操作）
            'media_play_continuously' => $videoConfig['play_continuously'], //音视频自动播放开关： on(开启)， off（关闭）
            'multiple_learn' => [
                'multiple_learn_enable' => empty($multipleLearnSetting['multiple_learn_enable']) ? 'off' : 'on', //是否开启多开 on|off
                'multiple_learn_kick_mode' => $multipleLearnSetting['multiple_learn_kick_mode'],
            ],
        ];
    }
}
