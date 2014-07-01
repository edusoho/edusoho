<?php

namespace Topxia\Service\Course\Dao;

interface HomeworkResultsDao
{   

	public function getHomeworkResultByHomeworkIdAndUserId($homeworkId, $userId);

	public function getHomeworkResultByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId);

    public function searchHomeworkResults($conditions, $orderBy, $start, $limit);

    public function searchHomeworkResultsCount($conditions);

    public function findHomeworkResultsByHomeworkIds($homeworkIds);

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId);
}