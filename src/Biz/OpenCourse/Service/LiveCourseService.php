<?php

namespace Biz\OpenCourse\Service;

interface LiveCourseService
{
    public function createLiveRoom($course, $lesson, $container);

    public function editLiveRoom($course, $lesson, $container);

    public function entryLive($params);

    public function checkLessonStatus($lesson);

    public function checkCourseUserRole($course, $lesson);

    public function findBeginingLiveCourse($afterSecond);
}
