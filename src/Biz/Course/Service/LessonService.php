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
     * @Log(module="course",action="create_lesson")
     */
    public function createLesson($fields);

    public function updateLesson($lessonId, $fields);

    public function publishLesson($courseId, $lessonId, $updateLessonNum = true);

    public function publishLessonByCourseId($courseId);

    public function unpublishLesson($courseId, $lessonId);

    /**
     * @param $courseId
     * @param $lessonId
     *
     * @return mixed
     * @Log(module="course",action="delete_lesson",param="lessonId")
     */
    public function deleteLesson($courseId, $lessonId);

    public function isLessonCountEnough($courseId);

    public function getLessonLimitNum();

    public function findLessonsByCourseId($courseId);

    public function setOptional($courseId, $lessonId);

    public function unsetOptional($courseId, $lessonId);

    /**
     * 重新计算 课时的 number 和 published_number
     */
    public function updateLessonNumbers($courseId);
}
