<?php

namespace Biz\Course\Service;

interface CourseService
{
	public function getCourseItems($courseId);

	public function tryManageCourse($courseId);
}