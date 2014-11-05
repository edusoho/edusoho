<?php

namespace Topxia\Service\Course\Dao;

interface HomeworkDao
{
    public function getHomework($id);

    public function findHomeworkByCourseIdAndLessonIds($courseId, $lessonIds);

    public function findHomeworksByCreatedUserId($userId);
    
    public function getHomeworkByLessonId($lessonId);

    public function addHomework($fields);

    public function updateHomework($id,$fields);

    public function deleteHomework($id);
}