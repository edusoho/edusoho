<?php

namespace Topxia\Service\Course\Dao;

interface HomeworkResultsDao
{   
    public function searchHomeworkResults($conditions, $orderBy, $start, $limit);

    public function searchHomeworkResultsCount($conditions);

    public function findHomeworkResultsByHomeworkIds($homeworkIds);

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId);
}