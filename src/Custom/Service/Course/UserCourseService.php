<?php 
namespace Custom\Service\Course;

interface UserCourseService{
	public function getUserCurrentlyLearnByCourseId($userId,$courseId);
}