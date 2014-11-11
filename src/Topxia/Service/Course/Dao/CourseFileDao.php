<?php
namespace Topxia\Service\Course\Dao;

interface CourseFileDao
{
	public function addCourseFile($courseFile);
	
	public function deleteCourseFileLink($userId, $fileId, $targetId);
}