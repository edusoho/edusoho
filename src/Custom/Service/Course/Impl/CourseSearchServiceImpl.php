<?php
namespace Custom\Service\Course\Impl;
use Custom\Service\Course\CourseSearchService;
use Topxia\Service\Common\BaseService;

class CourseSearchServiceImpl extends BaseService implements CourseSearchService
{
	public function searchCourses($conditions, $sort = 'latest', $start, $limit)
	{
		$conditions = $this->_prepareCourseConditions($conditions);
		if($sort == 'latest'){
			$orderBy = array('createdTime', 'DESC');
		}
		
		return CourseSerialize::unserializes($this->getCourseSearchDao()->searchCourses($conditions, $orderBy, $start, $limit));
	}
	public function searchCourseCount($conditions)
	{
		$conditions = $this->_prepareCourseConditions($conditions);
		return $this->getCourseSearchDao()->searchCourseCount($conditions);
	}

	private function _prepareCourseConditions($conditions)
	{
		$conditions = array_filter($conditions);

		if (isset($conditions['categoryId'])) {
			$childrenIds = $this->getCategoryService()->findCategoryChildrenIds($conditions['categoryId']);
			$conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
			unset($conditions['categoryId']);
		}
		return $conditions;
	}
	private function getCourseSearchDao ()
	{
	    return $this->createDao('Custom:Course.CourseSearchDao');
	}
	private function getCategoryService()
	{
		return $this->createService('Taxonomy.CategoryService');
	}

}
class CourseSerialize
{
    public static function unserialize(array $course = null)
    {
    	if (empty($course)) {
    		return $course;
    	}

	$course['tags'] = empty($course['tags']) ? array() : explode('|', trim($course['tags'], '|'));

	if(empty($course['goals'] )) {
		$course['goals'] = array();
	} else {
		$course['goals'] = explode('|', trim($course['goals'], '|'));
	}

	if(empty($course['audiences'] )) {
		$course['audiences'] = array();
	} else {
		$course['audiences'] = explode('|', trim($course['audiences'], '|'));
	}

	if(empty($course['teacherIds'] )) {
		$course['teacherIds'] = array();
	} else {
		$course['teacherIds'] = explode('|', trim($course['teacherIds'], '|'));
	}

	return $course;
    }

    public static function unserializes(array $courses)
    {
    	return array_map(function($course) {
    		return CourseSerialize::unserialize($course);
    	}, $courses);
    }
}