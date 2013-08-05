<?php

namespace Topxia\Service\Course\Dao;

interface LessonQuizDao
{
    public function addLessonQuiz($lessonQuizInfo);

    public function getLessonQuiz($id);

    public function getLessonQuizByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId);

    public function updateLessonQuiz($id, $fields);

    public function deleteLessonQuiz($id);
}