<?php

namespace Topxia\Service\Course\Dao;

interface LessonExtendDao
{
    public function getLesson($id);

    public function addLesson($fields);

    public function updateLesson($id, $fields);

    public function deleteLesson($id);

    public function deleteLessonsByCourseId($courseId);

}
