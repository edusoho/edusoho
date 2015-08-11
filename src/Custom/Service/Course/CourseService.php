<?php

namespace Custom\Service\Course;

/**
 * 课程服务接口.
**/
interface CourseService
{
	public function updateCourse($id, $fields);

	/**
	 * 根据课程id获取其它期课程.
	 * @param courseId 课程id
	 * @return 相关课程列表.
	**/
	public function findOtherPeriods($courseId);

}