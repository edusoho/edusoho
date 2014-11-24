<?php
namespace Custom\Service\TagCourse;

interface TagCourseService {
	/**
	* 根据标签id和课程状态获取该标签下学习的人数
	*
	*/
	public function getCourseStudentCountByTagIdAndCourseStatus($tagId,$status);
}