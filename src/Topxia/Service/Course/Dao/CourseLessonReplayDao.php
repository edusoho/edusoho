<?php

namespace Topxia\Service\Course\Dao;

interface CourseLessonReplayDao
{
	const TABLENAME = 'course_lesson_replay';

	public function addCourseLessonReplay($courseLessonReplay);

	public function getCourseLessonReplay($id);

	public function deleteLessonReplayByLessonId($lessonId);

	public function getCourseLessonReplayByLessonId($lessonId);

	public function deleteLessonReplayByCourseId($courseId);
}