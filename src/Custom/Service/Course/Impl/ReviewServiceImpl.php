<?php
namespace Custom\Service\Course\Impl;
use Topxia\Service\Course\Impl\ReviewServiceImpl as BaseReviewServiceImpl;
use Custom\Service\Course\ReviewService;
use Symfony\Component\HttpFoundation\File\File;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class ReviewServiceImpl extends BaseReviewServiceImpl implements ReviewService
{
	public function getReviewPost($id)
	{
		return $this->getReviewPostDao()->getReviewPost($id);
	}

	public function saveReviewPost($fields)
	{
		if (!ArrayToolkit::requireds($fields, array('courseId', 'reviewId','userId', 'content'))) {
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
		$reviewPost = $this->getReviewPostDao()->addReviewPost(array(
			'userId' => $fields['userId'],
			'courseId' => $fields['courseId'],
			'reviewId' => $fields['reviewId'],
			'content' => $fields['content'],
			'createdTime' => time(),
		));
		return $reviewPost;
	}

	public function updateReviewPost($id,$fields)
	{
		if (!ArrayToolkit::requireds($fields, array('content'))) {
			throw $this->createServiceException('参数不正确，评价失败！');
		}
		$post = $this->getReviewPostDao()->getReviewPost($id);
		if (empty($post)) {
			throw $this->createServiceException('回复不存在，更新失败！');
		}
		$post['content']=$fields['content'];
		return $this->getReviewPostDao()->updateReviewPost($id,$post);

		
	}

	public function findReviewPostsByReviewIds(array $reviewIds)
	{
		return $this->getReviewPostDao()->findReviewPostsByReviewIds($reviewIds);
	}

	public function deleteReviewPost($id)
	{
		return $this->getReviewPostDao()->deleteReviewPost($id);
	}
	
	private function getCourseService()
    {
    	return $this->createService('Course.CourseService');
    }

    private function getUserService()
    {
    	return $this->createService('User.UserService');
    }

	private function getReviewPostDao()
	{
		return $this->createDao('Custom:Course.ReviewPostDao');
	}

	private function getReviewDao()
    {
    	return $this->createDao('Course.ReviewDao');
    }
}