<?php

namespace AppBundle\Component\Export;

class ExportUtil
{
    public static function getExportCsvTitle($name)
    {
        $title = [
            'user-learn-statistics' => 'user.learn.statistics.user_learn_statistics',
            'user-lesson-statistics' => 'user.learn.statistics.user_lesson_statistics',
            'user-course-statistics' => 'user.learn.statistics.user_course_statistics',
            'information-collect-detail' => 'information_collect.detail',
        ];

        return empty($title[$name]) ? $name : $title[$name];
    }
}
