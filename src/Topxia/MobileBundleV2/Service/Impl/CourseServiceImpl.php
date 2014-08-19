<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\CourseService;

class CourseServiceImpl extends BaseService implements CourseService
{
	public function getVersion()
	{
		var_dump("CourseServiceImpl->getVersion");
		return $this->formData;
	}


	public function getCourses()
	{
		$conditions['status'] = 'published';
        		$conditions['type'] = 'normal';

		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
		$total = $this->controller->getCourseService()->searchCourseCount($conditions);

		$sort = $this->$this->getParam("start", "latest");
		$conditions['sort'] = $sort;
        		$courses = $this->controller->getCourseService()->searchCourses($conditions, $sort, $start, $limit);
		
		$result = array(
			"start"=>$start,
			"limit"=>$limit,
			"totla"=>$total,
			"data"=>$this->controller->filterCourses($courses)
			);

		return $result;
	}

	public function getLearningCourse()
	{
		$token = $this->controller->getUserToken($this->request);
		return $token;
	}
}