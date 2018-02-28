<?php

namespace Biz\Course\Service;

interface LessonService
{
    public function countLessons($courseId);

    public function publishLesson($lessonId);

    public function publishLessonByCourseId($courseId);

    public function unpublishLesson($lessonId);

    public function deleteLesson($lessonId);
}
