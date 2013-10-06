<?php
namespace Topxia\Service\Activity\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Activity\ThreadService;
use Topxia\Common\ArrayToolkit;

class ThreadServiceImpl extends BaseService implements ThreadService
{

	public function getThread($activityId, $threadId)
	{
		$thread = $this->getThreadDao()->getThread($threadId);
		if (empty($thread)) {
			return null;
		}
		return $thread['activityId'] == $activityId ? $thread : null;
	}

	public function findThreadsByType($activityId, $sort = 'latestCreated', $start, $limit)
	{
		if ($sort == 'latestPosted') {
			$orderBy = array('latestPosted', 'DESC');
		} else {
			$orderBy = array('createdTime', 'DESC');
		}
		return $this->getThreadDao()->findThreadsByActivityId($activityId, $orderBy, $start, $limit);
	}

    public function deleteThreadsByIds(array $ids=null){

        if(empty($ids)){
             throw $this->createServiceException("Please select thread item !");
        }

       	foreach ($ids as $id) {
            $this->getThreadDao()->deleteThread($id);
            $this->getThreadPostDao()->deletePostsByThreadId($id);
        }
        return true;
    }

	public function searchThreads($conditions, $sort, $start, $limit)
	{
		switch ($sort) {
			case 'created':
				$orderBys = array(
					array('isStick', 'DESC'),
					array('createdTime', 'DESC'),
				);
				break;
			case 'posted':
				$orderBys = array(
					array('isStick', 'DESC'),
					array('latestPostTime', 'DESC'),
				);
				break;
			case 'createdNotStick':
				$orderBys = array(
					array('createdTime', 'DESC'),
				);
				break;
			case 'postedNotStick':
				$orderBys = array(
					array('latestPostTime', 'DESC'),
				);
				break;
			default:
				throw $this->createServiceException('参数sort不正确。');
		}
		return $this->getThreadDao()->searchThreads($conditions, $orderBys, $start, $limit);
	}

	public function addThreadPostNum($threadId,$userId){

		$thread = $this->getThreadDao()->getThread($threadId);		

		if(empty($thread)){
			throw $this->createServiceException('问题不存在，操作失败！');
		}

		$field['postNum']=(int)$thread['postNum']+1;
		//$user=$this->getUserService()->getUser($userId);
		//if(empty($user)&&$user['id']>0){
			//ROLE_TEACHER
		//	$field['teacherPostNum']=(int)$thread['teacherPostNum']+1;
		//}
		return $this->getThreadDao()->updateThread($threadId,$field);
	}

	public function searchThreadCount($conditions)
	{
		return $this->getThreadDao()->searchThreadCount($conditions);
	}

	public function createThread($thread)
	{
		if (empty($thread['activityId'])) {
			throw $this->createServiceException('Course ID can not be empty.');
		}
		$thread['userId'] = $this->getCurrentUser()->id;
		// @todo filter it.
		$thread['title'] = empty($thread['title']) ? '' : $thread['title'];
		$thread['content'] = empty($thread['content']) ? '' : $thread['content'];
		$thread['createdTime'] = time();
		$thread['latestPostUserId'] = $thread['userId'];
		$thread['latesPostTime'] = $thread['createdTime'];
		return $this->getThreadDao()->addThread($thread);
	}

	public function deleteThread($activityId, $threadId)
	{
		$thread = $this->getThread($activityId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadPostDao()->deletePostsByThreadId($thread['id']);
		$this->getThreadDao()->deleteThread($thread['id']);
	}

	public function stickThread($activityId, $threadId)
	{
		$thread = $this->getThread($activityId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadDao()->updateThread($thread['id'], array('isStick' => 1));
	}

	public function unstickThread($activityId, $threadId)
	{
		$thread = $this->getThread($activityId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadDao()->updateThread($thread['id'], array('isStick' => 0));
	}

	public function eliteThread($activityId, $threadId)
	{
		$thread = $this->getThread($activityId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadDao()->updateThread($thread['id'], array('isElite' => 1));
	}

	public function uneliteThread($activityId, $threadId)
	{
		$thread = $this->getThread($activityId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadDao()->updateThread($thread['id'], array('isElite' => 0));
	}

	public function hitThread($activityId, $threadId)
	{
		$this->getThreadDao()->waveThread($threadId, 'hitNum', +1);
	}

	public function findThreadPosts($activityId, $threadId, $sort = 'default', $start, $limit)
	{
		$thread = $this->getThread($activityId, $threadId);
		if (empty($thread)) {
			return array();
		}
		if ($sort == 'best') {
			$orderBy = array('score', 'DESC');
		} else {
			$orderBy = array('createdTime', 'ASC');
		}

		return $this->getThreadPostDao()->findPostsByThreadId($threadId, $orderBy, $start, $limit);
	}

	public function getThreadPostCount($activityId, $threadId)
	{
		return $this->getThreadPostDao()->getPostCountByThreadId($threadId);
	}

	public function postThread($post)
	{
		$requiredKeys = array('activityId', 'threadId', 'content');
		if (!ArrayToolkit::requireds($post, $requiredKeys)) {
			throw $this->createServiceException(sprintf('参数缺失，必须包含参数： %s', implode(',', $requiredKeys)));
		}

		$thread = $this->getThread($post['activityId'], $post['threadId']);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('课程(ID: %s)话题(ID: %s)不存在。', $post['activityId'], $post['threadId']));
		}
		$threadId=$post['threadId'];
		$userId=$this->getCurrentUser()->id;
		$post['userId'] = $userId;
		$post['createdTime'] = time();
		$post = $this->getThreadPostDao()->addPost($post);
		$this->addThreadPostNum($threadId,$userId);


		// 高并发的时候， 这样更新postNum是有问题的，这里暂时不考虑这个问题。
		//$threadFields = array(
		//	'postNum' => $thread['postNum'] + 1,
		//	'latestPostUserId' => $post['userId'],
		///	'latestPostTime' => $post['createdTime'],
		//);
		//$this->getThreadDao()->updateThread($thread['id'], $threadFields);

		return $post;
	}

	public function deletePost($activityId, $id)
	{
		$post = $this->getThreadPostDao()->getPost($id);
		if (empty($post)) {
			throw $this->createServiceException(sprintf('帖子(#%s)不存在，删除失败。', $id));
		}

		if ($post['activityId'] != $activityId) {
			throw $this->createServiceException(sprintf('帖子#%s不属于课程#%s，删除失败。', $id, $activityId));
		}

		$this->getThreadPostDao()->deletePost($post['id']);
		$this->getThreadDao()->waveThread($post['threadId'], 'postNum', -1);
	}

	private function getThreadDao()
	{
		return $this->createDao('Activity.ThreadDao');
	}

	private function getThreadPostDao()
	{
		return $this->createDao('Activity.ThreadPostDao');
	}

	private function getActivityService()
	{
		return $this->createService('Activity.ActivityService');
	}

	private function getUserService()
    {
      	return $this->createService('User.UserService');
    }

}