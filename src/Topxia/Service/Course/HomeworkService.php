<?php

namespace Topxia\Service\Course;

interface HomeworkService
{
	public function getHomework($id);

    public function findHomeworksByLessonId($lessonId);

    public function findHomeworksByCourseIdAndLessonIds($courseId, $lessonIds);

    public function findHomeworksByCreatedUserId($userId);
    
    public function getResult($id);

    public function createHomework($courseId,$lessonId,$fields);

    public function updateHomework($id, $fields);

    public function removeHomework($id);
    
    public function showHomework($id);

    public function startHomework($id);

    public function checkHomework($id,$userId,$checkHomeworkData);

    public function submitHomework($id,$homework);

    public function saveHomework($id,$homework);

    //HomeworkResults

    public function getResultByHomeworkId($homeworkId);

    public function getResultByHomeworkIdAndUserId($homeworkId, $userId);

    public function getResultByLessonIdAndUserId($lessonId, $userId);

    public function searchResults($conditions, $orderBy, $start, $limit);

    public function searchResultsCount($conditions);

    public function findResultsByIds($homeworkIds);

    public function findResultsByLessonIdAndStatus($lessonId,$status);

    public function findResultsByLessonId($lessonId);

    public function findResultsByStatusAndCheckTeacherId($status,$checkTeacherId, $orderBy,$start, $limit);

    public function findResultsCountsByStatusAndCheckTeacherId($status,$checkTeacherId);

    public function findResultsByCourseIdAndStatus($courseId,$status,$orderBy,$start,$limit);

    public function findResultsCountsByCourseIdAndStatus($courseId,$status);

    //item
    public function findItemsByHomeworkId($homeworkId);

    /**
     * 获得作业的问题集（含子题）
     */
    public function getItemSetByHomeworkId($homeworkId);
    
    // public function getItemSetResultByHomeworkIdAndResultId($homeworkId,$resultId);

    public function getItemSetResultByHomeworkIdAndUserId($homeworkId,$userId);

    public function createHomeworkItems($homeworkId, $items);

}