<?php

namespace Biz\OpenCourse\Service;

interface LiveCourseService
{
    public function createLiveRoom($course, $lesson, $routes);

    public function editLiveRoom($course, $lesson, $routes);

    public function entryLive($params);

    public function checkLessonStatus($lesson);

    public function checkCourseUserRole($course, $lesson);

    public function isLiveFinished($lessonId);
}
