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

    const allowSettingNames = [
        SettingNames::COURSE_SETTING,
        SettingNames::LIVE_COURSE_SETTING,
        SettingNames::VIDEO_LEARN_SETTING,
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
}
