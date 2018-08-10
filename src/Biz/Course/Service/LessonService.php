<?php

namespace Biz\Course\Service;

use Biz\System\Annotation\Log;

interface LessonService
{
    public function getLesson($lessonId);

    public function countLessons($conditions);

    /**
     * @param $fields
     *
     * @return mixed
     * @Log(level="info",module="course",action="create_lesson",message="创建课时",targetType="course_task",param="result")
     */
    public function createLesson($fields);

    public function updateLesson($lessonId, $fields);

    public function publishLesson($courseId, $lessonId);

    public function publishLessonByCourseId($courseId);

    public function unpublishLesson($courseId, $lessonId);

    public function deleteLesson($courseId, $lessonId);

    public function isLessonCountEnough($courseId);

    public function getLessonLimitNum();

    public function findLessonsByCourseId($courseId);

    public function setOptional($courseId, $lessonId);

    public function unsetOptional($courseId, $lessonId);
}
