<?php

namespace AppBundle\Common;

class CourseToolkit
{
    /**
     * course_chapter 表中，有3种类型
     *   章  chapter
     *   节  unit
     *   课时 lesson
     *
     * 只显示给前台用户 章和节 2种类型
     */
    public static function getUserDisplayedChapterTypes()
    {
        return array('chapter', 'unit');
    }

    public static function getAvailableChapterTypes()
    {
        return array('chapter', 'unit', 'lesson');
    }
}
