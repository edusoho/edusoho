<?php

namespace Topxia\Service\Course\Dao;

interface HomeworkResultDao
{   
    public function getHomeworkResult($id);
    
    public function addHomeworkResult(array $fields);

    public function updateHomeworkResult($id,array $fields);

    public function getHomeworkResultByHomeworkId($homeworkId);
    
    public function getHomeworkResultByHomeworkIdAndUserId($homeworkId, $userId);

    public function getHomeworkResultByHomeworkIdAndStatusAndUserId($homeworkId, $status, $userId);

    public function getHomeworkResultByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId);

    public function searchHomeworkResults($conditions, $orderBy, $start, $limit);

    public function searchHomeworkResultsCount($conditions);

    public function findHomeworkResultsByHomeworkIds($homeworkIds);

    public function findHomeworkResultsByStatusAndUserId($userId, $status);

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId);

    public function findHomeworkResultsByCourseIdAndLessonIdAndStatus($courseId, $lessonId,$status);

    public function findHomeworkResultsByStatusAndCheckTeacherId($checkTeacherId, $status, $orderBy,$start, $limit);

    public function findHomeworkResultsCountsByStatusAndCheckTeacherId($checkTeacherId, $status);

    public function findHomeworkResultsByCourseIdAndStatus($courseId, $status,$orderBy, $start, $limit);

    public function findHomeworkResultsCountsByCourseIdAndStatus($courseId, $status);
}