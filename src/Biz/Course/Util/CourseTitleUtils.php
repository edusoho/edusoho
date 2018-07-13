<?php

namespace Biz\Course\Util;

class CourseTitleUtils
{
    public static function formatTitle($course, $courseSetTitle)
    {
        if (empty($course['title'])) {
            $course['title'] = $courseSetTitle;
        } else {
            $course['title'] = $courseSetTitle.'-'.$course['title'];
        }

        return $course;
    }
}
