<?php

namespace Topxia\Service\Course\Dao;

interface HomeworkResultDao
{   
    public function getResult($id);
    
    public function addResult(array $fields);

    public function updateResult($id,array $fields);

    public function getResultByHomeworkId($homeworkId);
    
    public function getResultByHomeworkIdAndUserId($homeworkId, $userId);

    public function getResultByHomeworkIdAndStatusAndUserId($homeworkId, $status, $userId);

    public function getResultByLessonIdAndUserId($lessonId, $userId);

    public function searchResults($conditions, $orderBy, $start, $limit);

    public function searchResultsCount($conditions);

    public function findResultsByHomeworkIds($homeworkIds);

    public function findResultsByStatusAndUserId($userId, $status);

    public function findResultsByLessonId($lessonId);

    public function findResultsByLessonIdAndStatus($lessonId,$status);

    public function findResultsByStatusAndCheckTeacherId($checkTeacherId, $status, $orderBy,$start, $limit);

    public function findResultsCountsByStatusAndCheckTeacherId($checkTeacherId, $status);

    public function findResultsByCourseIdAndStatus($courseId, $status,$orderBy, $start, $limit);

    public function findResultsCountsByCourseIdAndStatus($courseId, $status);
}