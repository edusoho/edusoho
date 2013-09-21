<?php

namespace Topxia\Service\Course\Dao;

interface CourseQuizItemDao
{
    public function addQuizItem($quizItemInfo);

    public function getQuizItem($id);

    public function updateQuizItem($id, $fields);

    public function deleteQuizItem($id);

    public function findQuizItemsByIds(array $ids);

    public function findItemIdsByCourseIdAndLessonId($courseId, $lessonId);
    
    public function findQuizItemsByCourseIdAndLessonId($courseId, $lessonId);

    public function getQuizItemsCount($courseId, $lessonId);
}