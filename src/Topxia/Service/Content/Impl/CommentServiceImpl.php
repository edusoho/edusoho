<?php
namespace Topxia\Service\Content\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Content\CommentService;

class CommentServiceImpl extends BaseService implements CommentService
{

	public function createComment(array $comment)
	{
		$this->checkCommentObjectFields($comment['objectType']);
		$this->checkCommentObjectValue($comment);
		$fields = array();
		$fields['objectType'] = $comment['objectType'];
		$fields['objectId'] = $comment['objectId'];
		$fields['content'] = $comment['content'];
		$fields['userId'] = $this->getCurrentUser()->id;
		$fields['createdTime'] = time();

		return $this->getCommentDao()->addComment($fields);
	}

	public function getComment($id)
	{
		return $this->getCommentDao()->getComment($id);
	}

	public function findComments($objectType, $objectId, $start, $limit)
	{	
		$this->checkCommentObjectFields($objectType);
		$this->checkCommentObjectValue(array('objectType'=>$objectType, 'objectId'=>$objectId));
		return $this->getCommentDao()->findCommentsByObjectTypeAndObjectId($objectType, $objectId, $start, $limit);
	}

	public function deleteComment($id)
	{
		$user = $this->getCurrentUser();
		$comment = $this->getComment($id);

		if (empty($comment)) {
			throw $this->createNotFoundException('评论不存在');
		}

		if (empty($user)) {
			throw $this->createAccessDeniedException('无权限删除评论！');
		}

		if ($comment['userId'] != $user['id'] and ! $this->getContainer()->get('security.context')->isGranted('ROLE_ADMIN')) {
			throw $this->createAccessDeniedException('无权限删除评论！');
		}

		return $this->getCommentDao()->deleteComment($id);
	}

	public function getCommentsByType($objectType, $start, $limit)
	{
		$this->checkCommentObjectFields($objectType);
		return $this->getCommentDao()->findCommentsByObjectType($objectType, $start, $limit);
	}

	public function getCommentsCountByType($objectType)
	{
		$this->checkCommentObjectFields($objectType);
		return $this->getCommentDao()->findCommentsCountByObjectType($objectType);
	}

	private function checkCommentObjectFields($objectType)
	{
		$objectTypes = array('course');
		if(!in_array($objectType, $objectTypes)){
			throw $this->createServiceException('不存在当前这种评论对象');
		}
	}
	
	//TODO 对于多种对象的评论应该实现检测评论的对象是否存在
	private function checkCommentObjectValue($comment)
	{
		switch ($comment['objectType']) {
			case self::COMMENT_OBJECTTYPE_COURSE:
				$foundCourse = $this->getCourseService()->getCourse($comment['objectId']);
				if(empty($foundCourse)){
					throw $this->createServiceException('评论课程失败，该课程不存在');
				}
				break;
			
			default:
				break;
		}
	}

	private function getCommentDao()
	{
        return $this->createDao('Content.CommentDao');
	}

	private function getCourseService()
    {
    	return $this->createService('Course.CourseService');
    }
}
