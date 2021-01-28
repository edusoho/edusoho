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

    public static function getDisplayedTitle($course)
    {
        if (!empty($course['courseSetTitle'])) {
            if (empty($course['title'])) {
                return $course['courseSetTitle'];
            } else {
                return $course['courseSetTitle'].'-'.$course['title'];
            }
        }

        return null;
    }
}
