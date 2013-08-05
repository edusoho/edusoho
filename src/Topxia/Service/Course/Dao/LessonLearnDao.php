<?php

namespace Topxia\Service\Course\Dao;

interface LessonLearnDao
{
	public function getLearn($id);

	public function getLearnByUserIdAndLessonId($userId, $lessonId);

	public function findLearnsByUserIdAndCourseId($userId, $courseId);

	public function findLearnsByUserIdAndCourseIdAndStatus($userId, $courseId, $status);

	public function getLearnCountByUserIdAndCourseIdAndStatus($userId, $courseId, $status);

	public function addLearn($learn);

	public function updateLearn($id, $fields);
}