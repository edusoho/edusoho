<?php

namespace Topxia\Service\Course\Dao;

interface LessonLearnDao
{
    public function getLearn($id);

    public function getLearnByUserIdAndLessonId($userId, $lessonId);

    public function findLearnsByUserIdAndCourseId($userId, $courseId);

    public function findLearnsByUserIdAndCourseIdAndStatus($userId, $courseId, $status);

    public function getLearnCountByUserIdAndCourseIdAndStatus($userId, $courseId, $status);

    public function findLearnsByLessonId($lessonId, $start, $limit);

    public function findLearnsCountByLessonId($lessonId);

    public function findLatestFinishedLearns($start, $limit);

    public function addLearn($learn);

    public function updateLearn($id, $fields);

    public function deleteLearnsByLessonId($lessonId);

    public function searchLearnCount($conditions);

    public function searchLearnTime($conditions);
    
    public function searchWatchTime($conditions);

    public function searchLearns($conditions,$orderBy,$start,$limit);

    public function analysisLessonFinishedDataByTime($startTime,$endTime);
}