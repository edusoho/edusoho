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

	/**
	 * 根据id获取课程.
	 * @param 课程id.
	 * @throws ServiceException 当id为空或者指定id的课程不存在.
	**/
	public function loadCourse($id);

	/**
	 * 根据id获取课时.
	 * @param 课时id.
	 * @throws ServiceException 当id为空或者指定id的课时不存在.
	**/
	public function loadLesson($id);

}