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


	public function favoriteCourse()
	{
        		$user = $this->controller->getUserByToken($this->request);
        		$courseId = $this->getParam("courseId");

        		if (empty($user) || !$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录，不能收藏课程！");
        		}

        		if (!$this->controller->getCourseService()->hasFavoritedCourse($courseId)) {
            		$this->controller->getCourseService()->favoriteCourse($courseId);
        		}

        		return true;
	}

	public function getTeacherCourses()
	{
		$userId = $this->getParam("userId");
		if (empty($userId)) {
			return array();
		}
		$courses = $this->controller->getCourseService()->findUserTeachCourses(
	            	$userId, 0, 10
	        	);

		return $courses;
	}

	public function unFavoriteCourse()
	{
		$user = $this->controller->getUserByToken($this->request);
        		$courseId = $this->getParam("courseId");

        		if (empty($user) || !$user->isLogin()) {
            		return $this->createErrorResponse('not_login', "您尚未登录，不能收藏课程！");
        		}

        		if (!$this->controller->getCourseService()->hasFavoritedCourse($courseId)) {
            		return $this->createErrorResponse('runtime_error', "您尚未收藏课程，不能取消收藏！");
        		}

        		$this->controller->getCourseService()->unfavoriteCourse($courseId);

        		return true;
	}

	public function getCourse()
	{	
		$user = $this->controller->getUserByToken($this->request);
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

		$vipLevels = $this->controller->getLevelService()->searchLevels(array('enabled' => 1), 0, 100);

		$member = $user->isLogin() ? $this->controller->getCourseService()->getCourseMember($course['id'], $user['id']) : null;
        		if ($member) {
            		$member['createdTime'] = date('c', $member['createdTime']);
        		}

        		return array(
        			"userIsStudent"=>$userIsStudent,
        			"course"=>$this->controller->filterCourse($course),
        			"userFavorited"=>$userFavorited,
        			"member"=>$member,
        			"vipLevels"=>$vipLevels
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