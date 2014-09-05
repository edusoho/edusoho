<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\LessonService;

class LessonServiceImpl extends BaseService implements LessonService
{
	public function getCourseLessons()
	{
		$token = $this->controller->getUserToken($this->request);
		$user = $this->controller->getUser();
		$courseId = $this->getParam("courseId");

		$lessons = $this->controller->getCourseService()->getCourseItems($courseId);
		$lessons = $this->controller->filterItems($lessons);
		if ($user->isLogin()) {
			$learnStatuses = $this->controller->getCourseService()->getUserLearnLessonStatuses($user['id'], $courseId)
		} else {
			$learnStatuses = array();
		}
		return array_values($lessons);
	}
}