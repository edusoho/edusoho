<?php

namespace Topxia\Service\Course\Dao;

interface CourseQuizDao
{
    public function addQuiz($lessonQuizInfo);

    public function getQuiz($id);

    public function updateQuiz($id, $fields);

    public function deleteQuiz($id);
    
    public function getQuizByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId);
}