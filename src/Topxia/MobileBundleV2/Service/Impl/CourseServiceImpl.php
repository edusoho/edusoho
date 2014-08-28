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

	public function getReviews()
	{
		$courseId = $this->getParam("courseId");
		$reviews = $this->controller->getReviewService()->findCourseReviews($courseId, 0, 3);
		$reviews = $this->controller->filterReviews($reviews);
		return $reviews;
	}

	public function getCourse()
	{	
		$token = $this->controller->getUserToken($this->request);
		$user = $this->controller->getUser();
		$courseId = $this->getParam("courseId");
		$course = $this->controller->getCourseService()->getCourse($courseId);
		if (empty($course)) {
		            $error = array('error' => 'not_found', 'message' => "课程#{$courseId}不存在。");
		            return $error;
		}

        		if ($course['status'] != 'published') {
            		$error = array('error' => 'course_not_published', 'message' => "课程#{$courseId}未发布或已关闭。");
            		return $error;
        		}

        		$userIsStudent = $user->isLogin() ? $this->controller->getCourseService()->isCourseStudent($courseId, $user['id']) : false;
        		$userFavorited = $user->isLogin() ? $this->controller->getCourseService()->hasFavoritedCourse($courseId) : false;
		$member = $user->isLogin() ? $this->controller->getCourseService()->getCourseMember($course['id'], $user['id']) : null;
        		if ($member) {
            		$member['createdTime'] = date('c', $member['createdTime']);
        		}

        		return array(
        			"userIsStudent"=>$userIsStudent,
        			"course"=>$this->controller->filterCourse($course),
        			"userFavorited"=>$userFavorited,
        			"member"=>$member
        			);
	}

	public function searchCourse()
	{
		$search = $this->getParam("search", '');
		$conditions['title'] = $search;
		return $this->findCourseByConditions($conditions);
	}

	public function getCourses()
	{
		$categoryId = (int) $this->getParam("categoryId", 0);
		$conditions['categoryId'] = $categoryId;
		return $this->findCourseByConditions($conditions);
	}

	private function findCourseByConditions($conditions)
	{
		$conditions['status'] = 'published';
        		$conditions['type'] = 'normal';

		$start = (int) $this->getParam("start", 0);
		$limit = (int) $this->getParam("limit", 10);
		$total = $this->controller->getCourseService()->searchCourseCount($conditions);

		$sort = $this->getParam("sort", "latest");
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