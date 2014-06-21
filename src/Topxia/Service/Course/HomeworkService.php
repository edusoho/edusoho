<?php

namespace Topxia\Service\Course;

interface HomeworkService
{
	public function getHomework($id);

    public function getHomeworkByCourseIdAndLessonId($courseId, $lessonId);

	public function getHomeworkResult($id);

	public function searchHomeworks($conditions, $sort, $start, $limit);

	public function createHomework($courseId,$lessonId,$fields);

    public function updateHomework($id, $fields);

    public function deleteHomework($id);

    public function deleteHomeworksByCourseId($courseId);

    //HomeworkResults

    public function searchHomeworkResults($conditions, $orderBy, $start, $limit);

    public function findHomeworkResultsByCourseIdAndLessonId($courseId, $lessonId);

    public function findHomeworkResultsByStatusAndCheckTeacherId($checkTeacherId, $status);

    public function findHomeworkResultsByCourseIdAndStatusAndCheckTeacherId($courseId,$checkTeacherId, $status);

    public function findHomeworkResultsByStatusAndStatusAndUserId($userId, $status);

    public function findAllHomeworksByCourseId($courseId);

    //item
    public function getHomeworkItems($homeworkId);

    public function updateHomeworkItems($homeworkId, $items);

    public function createHomeworkItems($homeworkId, $items);
}