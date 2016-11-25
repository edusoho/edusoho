<?php

namespace Biz\Course\Service;

interface CourseService
{
	public function getCourseItems($courseId);

	public function tryManageCourse($courseId);

	public function getNextNumberAndParentId($courseId);

	public function tryTakeCourse($courseId);

	public function isCourseStudent($courseId, $userId);
}