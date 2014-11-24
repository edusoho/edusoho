<?php

namespace Custom\Service\TagCourse\Dao;

interface TagCourseDao
{
		public function getCourseStudentCountByTagIdAndCourseStatus($tagId,$status);
}