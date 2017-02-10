<?php

namespace Topxia\Service\Course;

interface CourseDeleteService
{
    public function delete($courseId,$type);

    public function deleteLessonResult($lessonId);
}
