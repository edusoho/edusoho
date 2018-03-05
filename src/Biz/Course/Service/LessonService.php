<?php

namespace Biz\Course\Service;

interface LessonService
{
    public function countLessons($conditions);

    public function createLesson($fields);

    public function updateLesson($lessonId, $fields);

    public function publishLesson($courseId, $lessonId);

    public function publishLessonByCourseId($courseId);

    public function unpublishLesson($courseId, $lessonId);

    public function deleteLesson($courseId, $lessonId);

    public function isLessonCountEnough($courseId);
}
