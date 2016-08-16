<?php

namespace Topxia\Service\Course;

interface LiveCourseService
{
    public function createLiveRoom($course, $lesson, $container);

    public function editLiveRoom($course, $lesson, $container);

    public function entryLive($params);

    public function checkLessonStatus($lesson);

    public function checkCourseUserRole($lesson);

    public function generateLessonReplay($course, $lesson);

}
