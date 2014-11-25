<?php 
namespace Custom\Service\Course;

interface CourseSearchService{
	public function searchCourses($conditions, $sort = 'latest', $start, $limit);

	public function searchCourseCount($conditions);
}