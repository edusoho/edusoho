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

	public function deleteReviewsByCourseId($courseId)
	{
		return $this->getReviewDao()->deleteReviewsByCourseId($courseId);
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
		if(isset($conditions['keywordType'])){
			if($conditions['keywordType'] == 'title'){
				$conditions['title'] = "%{$conditions['keyword']}%";
				unset($conditions['keywordType']);
				unset($conditions['keyword']);
			} elseif ($conditions['keywordType'] == 'content'){
				$conditions['content'] = "%{$conditions['keyword']}%";
				unset($conditions['keywordType']);
				unset($conditions['keyword']);
			}
		}
		return $this->getReviewDao()->searchReviews($conditions, $sort, $start, $limit);
	}

	public function searchReviewsCount($conditions)
	{		
		if(isset($conditions['keywordType'])){
			if($conditions['keywordType'] == 'title'){
				$conditions['title'] = "%{$conditions['keyword']}%";
				unset($conditions['keywordType']);
				unset($conditions['keyword']);
			} elseif ($conditions['keywordType'] == 'content'){
				$conditions['content'] = "%{$conditions['keyword']}%";
				unset($conditions['keywordType']);
				unset($conditions['keyword']);
			}
		}
		return $this->getReviewDao()->searchReviewsCount($conditions);
	}
	
	public function saveReview($fields)
	{
		if (!ArrayToolkit::requireds($fields, array('courseId', 'userId', 'rating'))) {
			throw $this->createServiceException('参数不正确，评价失败！');
		}

		$course = $this->getCourseService()->tryTakeCourse($fields['courseId']);
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
				'title' => empty($fields['title']) ? '' : $fields['title'],
				'content' => empty($fields['content']) ? '' : $fields['content'],
				'createdTime' => time(),
			));
		} else {
			$review = $this->getReviewDao()->updateReview($review['id'], array(
				'rating' => $fields['rating'],
				'title' => empty($fields['title']) ? '' : $fields['title'],
				'content' => empty($fields['content']) ? '' : $fields['content'],
			));
		}

		$ratingSum = $this->getReviewDao()->getReviewRatingSumByCourseId($course['id']);
		$ratingNum = $this->getReviewDao()->getReviewCountByCourseId($course['id']);

		$this->getCourseService()->updateCourseCounter($course['id'], array(
			'rating' => $ratingSum / $ratingNum,
			'ratingNum' => $ratingNum,
		));

		return $review;
	}
	
	public function deleteReviewsByIds(array $ids=null)
	{
		if(empty($ids)){
            throw $this->createServiceException("Please select review item !");
        }
		return $this->getReviewDao()->deleteReviewsByIds($ids);
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

}