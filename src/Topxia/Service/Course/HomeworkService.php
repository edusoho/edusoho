<?php

namespace Topxia\Service\Course;

interface HomeworkService
{
	public function getHomework($id);

    public function findHomeworksByCourseIdAndLessonId($courseId, $lessonId);

    public function findHomeworksByCourseIdAndLessonIds($courseId, $lessonIds);

    public function findHomeworksByCreatedUserId($userId);
    
	public function getHomeworkResult($id);

	public function searchHomeworks($conditions, $sort, $start, $limit);

	public function createHomework($courseId,$lessonId,$fields);

    public function updateHomework($id, $fields);

    public function removeHomework($id);
    
    public function showHomework($id);

    public function startHomework($id);

    public function checkHomework($id,$userId,$checkHomeworkData);

    public function submitHomework($id,$homework);

    public function saveHomework($id,$homework);

    //HomeworkResults

    public function getHomeworkResultByHomeworkId($homeworkId);

    public function getHomeworkResultByHomeworkIdAndUserId($homeworkId, $userId);

    public function getHomeworkResultByCourseIdAndLessonIdAndUserId($courseId, $lessonId, $userId);

    public function searchHomeworkResults($conditions, $orderBy, $start, $limit);

    public function searchHomeworkResultsCount($conditions);

    public function findHomeworkResultsByHomeworkIds($homeworkIds);

    public function findHomeworkResultsByCourseIdAndLessonIdAndStatus($courseId, $lessonId,$status);

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId);

    public function findHomeworkResultsByStatusAndCheckTeacherId($status,$checkTeacherId, $orderBy,$start, $limit);

    public function findHomeworkResultsCountsByStatusAndCheckTeacherId($status,$checkTeacherId);

    public function findHomeworkResultsByCourseIdAndStatus($courseId,$status,$orderBy,$start,$limit);

    public function findHomeworkResultsCountsByCourseIdAndStatus($courseId,$status);

    //item
    public function findHomeworkItemsByHomeworkId($homeworkId);

    /**
     * 获得作业的问题集（含子题）
     */
    public function getItemSetByHomeworkId($homeworkId);
    
    // public function getItemSetResultByHomeworkIdAndResultId($homeworkId,$resultId);

    public function getItemSetResultByHomeworkIdAndUserId($homeworkId,$userId);

    public function createHomeworkItems($homeworkId, $items);

}