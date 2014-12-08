<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\ReviewService;
use Topxia\Common\ArrayToolkit;

class ReviewServiceImpl extends BaseService implements ReviewService
{

	public function getReview($id)
	{
		return $this->getReviewDao()->getReview($id);
	}

	public function findCourseReviews($courseId, $start, $limit)
	{
		return $this->getReviewDao()->findReviewsByCourseId($courseId, $start, $limit);
	}

	public function getCourseReviewCount($courseId)
	{
		return $this->getReviewDao()->getReviewCountByCourseId($courseId);
	}

	public function getUserCourseReview($userId, $courseId)
	{
		$user = $this->getUserService()->getUser($userId);
		if(empty($user)){
			throw $this->createServiceException("User is not Exist!");
		}
		$course = $this->getCourseService()->getCourse($courseId);
		if(empty($course)){
			throw $this->createServiceException("Course is not Exist!");
		}
		return $this->getReviewDao()->getReviewByUserIdAndCourseId($userId, $courseId);
	}

	public function searchReviews($conditions, $sort= 'latest', $start, $limit)
	{	
		if($sort=='latest'){
			$orderBy = array('createdTime', 'DESC');
		} else {
			$orderBy = array('rating','DESC');
		} 
		$conditions = $this->prepareReviewSearchConditions($conditions);
		return $this->getReviewDao()->searchReviews($conditions, $orderBy, $start, $limit);
	}

	public function searchReviewsCount($conditions)
	{		
		$conditions = $this->prepareReviewSearchConditions($conditions);
		return $this->getReviewDao()->searchReviewsCount($conditions);
	}

	private function prepareReviewSearchConditions($conditions)
	{
		$conditions = array_filter($conditions);

        if (isset($conditions['author'])) {
        	$author = $this->getUserService()->getUserByNickname($conditions['author']);
        	$conditions['userId'] = $author ? $author['id'] : -1;
        }

        return $conditions;
	}
	
	public function saveReview($fields)
	{
		if (!ArrayToolkit::requireds($fields, array('courseId', 'userId', 'rating'))) {
			throw $this->createServiceException('参数不正确，评价失败！');
		}

		list($course, $member) = $this->getCourseService()->tryTakeCourse($fields['courseId']);

		$userId = $this->getCurrentUser()->id;

		if (empty($course)) {
			throw $this->createServiceException("课程(#{$fields['courseId']})不存在，评价失败！");
		}
		$user = $this->getUserService()->getUser($fields['userId']);
		if (empty($user)) {
			return $this->createServiceException("用户(#{$fields['userId']})不存在,评价失败!");
		}

		$review = $this->getReviewDao()->getReviewByUserIdAndCourseId($user['id'], $course['id']);
		if (empty($review)) {
			$review = $this->getReviewDao()->addReview(array(
				'userId' => $fields['userId'],
				'courseId' => $fields['courseId'],
				'rating' => $fields['rating'],
				'content' => empty($fields['content']) ? '' : $fields['content'],
				'createdTime' => time(),
			));
		} else {
			$review = $this->getReviewDao()->updateReview($review['id'], array(
				'rating' => $fields['rating'],
				'content' => empty($fields['content']) ? '' : $fields['content'],
			));
		}

		$this->calculateCourseRating($course['id']);

		return $review;
	}

	public function createInitReview($fields)
	{
		$course = $this->getCourseService()->tryManageCourse($fields['courseId']);
		if($course['status'] != 'published') {
			$result = array(
				'status' => 'fail',
				'message' => '请先发布课程！',
			);
		} else {
			$user = $this->register($fields);
			$this->getCourseService()->becomeStudent($fields['courseId'], $user['id']);
			$fields['userId'] = $user['id'];
			$review = $this->saveReview($fields);
			$result = array(
				'status' => 'ok',
				'message' => '添加初始评价成功！',
			);
		}

		return $result;
	}

	public function deleteReview($id)
	{
		$review = $this->getReview($id);
		if (empty($review)) {
			throw $this->createServiceException("评价(#{$id})不存在，删除失败！");
		}

		$this->getReviewDao()->deleteReview($id);

		$this->calculateCourseRating($review['courseId']);

		$this->getLogService()->info('review', 'delete', "删除评价#{$id}");
	}

	private function calculateCourseRating($courseId)
	{
		$ratingSum = $this->getReviewDao()->getReviewRatingSumByCourseId($courseId);
		$ratingNum = $this->getReviewDao()->getReviewCountByCourseId($courseId);

		$this->getCourseService()->updateCourseCounter($courseId, array(
			'rating' => $ratingNum ? $ratingSum / $ratingNum : 0,
			'ratingNum' => $ratingNum,
		));
	}


	private function isCurrentUser($userId){
		$user = $this->getCurrentUser();
		if($userId==$user->id){
			return true;
		}
		return false;
	}

	private function register($fields)
	{
		$uniqueEamil = uniqid() . '@edusoho.com';
		$registration = array(
			'email' => $uniqueEamil,
			'nickname' => $fields['nickname'],
			'password' => 'qwertyuio',
		);
		$user = $this->getUserService()->register($registration);
		return $user;
	}

	private function getReviewDao()
    {
    	return $this->createDao('Course.ReviewDao');
    }

    private function getUserService()
    {
    	return $this->createService('User.UserService');
    }

    private function getCourseService()
    {
    	return $this->createService('Course.CourseService');
    }

    private function getLogService()
    {
    	return $this->createService('System.LogService');
    }

}