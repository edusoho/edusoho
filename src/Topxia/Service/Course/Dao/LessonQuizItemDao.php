<?php

namespace Topxia\Service\Course\Dao;

interface LessonQuizItemDao
{
    public function addLessonQuizItem($lessonQuizItemInfo);

    public function getLessonQuizItem($id);

    public function findLessonQuizItemsByCourseIdAndLessonId($courseId, $lessonId);

    public function findItemIdsByCourseIdAndLessonId($courseId, $lessonId);

    public function updateLessonQuizItem($id, $fields);

    public function deleteLessonQuizItem($id);

    public function findLessonQuizItemsByIds(array $ids);
}