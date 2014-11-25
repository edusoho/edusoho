<?php 
namespace Custom\Service\Course\Dao;
interface CourseSearchDao
{
	public function searchCourses($conditions, $sort = 'latest', $start, $limit);

	public function searchCourseCount($conditions);
}