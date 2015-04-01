<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\ThreadService;
use Topxia\Common\ArrayToolkit;

class ThreadServiceImpl extends BaseService implements ThreadService
{

	public function getThread($courseId, $threadId)
	{
		$thread = $this->getThreadDao()->getThread($threadId);
		if (empty($thread)) {
			return null;
		}
		return $thread['courseId'] == $courseId ? $thread : null;
	}

	public function findThreadsByType($courseId, $type, $sort = 'latestCreated', $start, $limit)
	{
		if ($sort == 'latestPosted') {
			$orderBy = array('latestPosted', 'DESC');
		} else {
			$orderBy = array('createdTime', 'DESC');
		}

		if (!in_array($type, array('question', 'discussion'))) {
			$type = 'all';
		}

		if ($type == 'all') {
			return $this->getThreadDao()->findThreadsByCourseId($courseId, $orderBy, $start, $limit);
		}

		return $this->getThreadDao()->findThreadsByCourseIdAndType($courseId, $type, $orderBy, $start, $limit);
	}

	public function findLatestThreadsByType($type, $start, $limit)
	{
		return $this->getThreadDao()->findLatestThreadsByType($type, $start, $limit);
	}

	public function findEliteThreadsByType($type, $status, $start, $limit)
	{
		return $this->getThreadDao()->findEliteThreadsByType($type, $status, $start, $limit);
	}

	public function searchThreads($conditions, $sort, $start, $limit)
	{
		
		$orderBys = $this->filterSort($sort);
		$conditions = $this->prepareThreadSearchConditions($conditions);
		return $this->getThreadDao()->searchThreads($conditions, $orderBys, $start, $limit);
	}


	public function searchThreadCount($conditions)
	{	
		$conditions = $this->prepareThreadSearchConditions($conditions);
		return $this->getThreadDao()->searchThreadCount($conditions);
	}

	public function searchThreadCountInCourseIds($conditions)
	{
		$conditions = $this->prepareThreadSearchConditions($conditions);
		return $this->getThreadDao()->searchThreadCountInCourseIds($conditions);
	}

	public function searchThreadInCourseIds($conditions, $sort, $start, $limit)
	{
		$orderBys = $this->filterSort($sort);
		$conditions = $this->prepareThreadSearchConditions($conditions);
		return $this->getThreadDao()->searchThreadInCourseIds($conditions, $orderBys, $start, $limit);
	}
	
	private function filterSort($sort)
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
			case 'popular':
				$orderBys = array(
					array('hitNum', 'DESC'),
				);
				break;

			default:
				throw $this->createServiceException('参数sort不正确。');
		}
		return $orderBys;
	}

	private function prepareThreadSearchConditions($conditions)
	{

		if(empty($conditions['type'])) {
			unset($conditions['type']);
		}

		if(empty($conditions['keyword'])) {
			unset($conditions['keyword']);
			unset($conditions['keywordType']);
		}

		if(empty($conditions['threadType'])){
			unset($conditions['threadType']);
		}

		if(isset($conditions['threadType'])){
			$conditions[$conditions['threadType']] = 1;
			unset($conditions['threadType']);
		}

		if (isset($conditions['keywordType']) && isset($conditions['keyword'])) {
			if (!in_array($conditions['keywordType'], array('title', 'content', 'courseId', 'courseTitle'))) {
				throw $this->createServiceException('keywordType参数不正确');
			}
			$conditions[$conditions['keywordType']] = $conditions['keyword'];
			unset($conditions['keywordType']);
			unset($conditions['keyword']);
		}

		if(empty($conditions['author'])) {
			unset($conditions['author']);
		}

		if (isset($conditions['author'])) {
			$author = $this->getUserService()->getUserByNickname($conditions['author']);
			$conditions['userId'] = $author ? $author['id'] : -1;
		}

		return $conditions;
	}

	public function createThread($thread)
	{
		if (empty($thread['courseId'])) {
			throw $this->createServiceException('Course ID can not be empty.');
		}
		if (empty($thread['type']) or !in_array($thread['type'], array('discussion', 'question'))) {
			throw $this->createServiceException(sprintf('Thread type(%s) is error.', $thread['type']));
		}

		list($course, $member) = $this->getCourseService()->tryTakeCourse($thread['courseId']);

		$thread['userId'] = $this->getCurrentUser()->id;
		$thread['title'] = $this->purifyHtml(empty($thread['title']) ? '' : $thread['title']);

		//创建thread过滤html
		$thread['content'] = $this->purifyHtml($thread['content']);
		$thread['createdTime'] = time();
		$thread['latestPostUserId'] = $thread['userId'];
		$thread['latestPostTime'] = $thread['createdTime'];
		$thread['private'] = $course['status'] == 'published' ? 0 : 1;
		$thread = $this->getThreadDao()->addThread($thread);

		foreach ($course['teacherIds'] as $teacherId) {
			if ($teacherId == $thread['userId']) {
				continue;
			}

			if ($thread['type'] != 'question') {
				continue;
			}

			$this->getNotifiactionService()->notify($teacherId, 'thread', array(
				'threadId' => $thread['id'],
				'threadUserId' => $thread['userId'],
				'threadUserNickname' => $this->getCurrentUser()->nickname,
				'threadTitle' => $thread['title'],
				'threadType' => $thread['type'],
				'courseId' => $course['id'],
				'courseTitle' => $course['title'],
			));
		}

		return $thread;
	}

	public function updateThread($courseId, $threadId, $fields)
	{
		$thread = $this->getThread($courseId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException('话题不存在，更新失败！');
		}

		$user = $this->getCurrentUser();
		($user->isLogin() and $user->id == $thread['userId']) or $this->getCourseService()->tryManageCourse($courseId);

		$fields = ArrayToolkit::parts($fields, array('title', 'content'));
		if (empty($fields)) {
			throw $this->createServiceException('参数缺失，更新失败。');
		}

		//更新thread过滤html
		$fields['content'] = $this->purifyHtml($fields['content']);
		return $this->getThreadDao()->updateThread($threadId, $fields);
	}

	public function deleteThread($threadId)
	{
		$thread = $this->getThreadDao()->getThread($threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $threadId));
		}

		if (!$this->getCourseService()->canManageCourse($thread['courseId'])) {
			throw $this->createServiceException('您无权限删除该话题');
		}

		$this->getThreadPostDao()->deletePostsByThreadId($threadId);
		$this->getThreadDao()->deleteThread($threadId);

		$this->getLogService()->info('thread', 'delete', "删除话题 {$thread['title']}({$thread['id']})");
	}

	public function stickThread($courseId, $threadId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$thread = $this->getThread($courseId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadDao()->updateThread($thread['id'], array('isStick' => 1));
	}

	public function unstickThread($courseId, $threadId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$thread = $this->getThread($courseId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadDao()->updateThread($thread['id'], array('isStick' => 0));
	}

	public function eliteThread($courseId, $threadId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$thread = $this->getThread($courseId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadDao()->updateThread($thread['id'], array('isElite' => 1));
	}

	public function uneliteThread($courseId, $threadId)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$thread = $this->getThread($courseId, $threadId);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('话题(ID: %s)不存在。', $thread['id']));
		}

		$this->getThreadDao()->updateThread($thread['id'], array('isElite' => 0));
	}

	public function hitThread($courseId, $threadId)
	{
		$this->getThreadDao()->waveThread($threadId, 'hitNum', +1);
	}

	public function findThreadPosts($courseId, $threadId, $sort = 'default', $start, $limit)
	{
		$thread = $this->getThread($courseId, $threadId);
		if (empty($thread)) {
			return array();
		}
		if ($sort == 'best') {
			$orderBy = array('score', 'DESC');
		} else if($sort == 'elite') {
			$orderBy = array('createdTime', 'DESC', ',isElite', 'ASC');
		} else {
			$orderBy = array('createdTime', 'ASC');
		}

		return $this->getThreadPostDao()->findPostsByThreadId($threadId, $orderBy, $start, $limit);
	}

	public function getThreadPostCount($courseId, $threadId)
	{
		return $this->getThreadPostDao()->getPostCountByThreadId($threadId);
	}

	public function findThreadElitePosts($courseId, $threadId, $start, $limit)
	{
		return $this->getThreadPostDao()->findPostsByThreadIdAndIsElite($threadId, 1, $start, $limit);
	}

	public function getPostCountByuserIdAndThreadId($userId,$threadId)
	{
		return $this->getThreadPostDao()->getPostCountByuserIdAndThreadId($userId,$threadId);
	}

	public function getThreadPostCountByThreadId($threadId)
	{
		return $this->getThreadPostDao()->getPostCountByThreadId($threadId);
	}

	public function getPost($courseId, $id)
	{
		$post = $this->getThreadPostDao()->getPost($id);
		if (empty($post) or $post['courseId'] != $courseId) {
			return null;
		}
		return $post;
	}

	public function createPost($post)
	{
		$requiredKeys = array('courseId', 'threadId', 'content');
		if (!ArrayToolkit::requireds($post, $requiredKeys)) {
			throw $this->createServiceException('参数缺失');
		}

		$thread = $this->getThread($post['courseId'], $post['threadId']);
		if (empty($thread)) {
			throw $this->createServiceException(sprintf('课程(ID: %s)话题(ID: %s)不存在。', $post['courseId'], $post['threadId']));
		}

		list($course, $member) = $this->getCourseService()->tryTakeCourse($post['courseId']);

		$post['userId'] = $this->getCurrentUser()->id;
		$post['isElite'] = $this->getCourseService()->isCourseTeacher($post['courseId'], $post['userId']) ? 1 : 0;
		$post['createdTime'] = time();

		//创建post过滤html
		$post['content'] = $this->purifyHtml($post['content']);
		$post = $this->getThreadPostDao()->addPost($post);

		// 高并发的时候， 这样更新postNum是有问题的，这里暂时不考虑这个问题。
		$threadFields = array(
			'postNum' => $thread['postNum'] + 1,
			'latestPostUserId' => $post['userId'],
			'latestPostTime' => $post['createdTime'],
		);
		$this->getThreadDao()->updateThread($thread['id'], $threadFields);

		return $post;
	}

	public function updatePost($courseId, $id, $fields)
	{
		$post = $this->getPost($courseId, $id);
		if (empty($post)) {
			throw $this->createServiceException("回帖#{$id}不存在。");
		}

		$user = $this->getCurrentUser();
		($user->isLogin() and $user->id == $post['userId']) or $this->getCourseService()->tryManageCourse($courseId);


		$fields  = ArrayToolkit::parts($fields, array('content'));
		if (empty($fields)) {
			throw $this->createServiceException('参数缺失。');
		}

		//更新post过滤html
		$fields['content'] = $this->purifyHtml($fields['content']);
		return $this->getThreadPostDao()->updatePost($id, $fields);
	}

	public function deletePost($courseId, $id)
	{
		$course = $this->getCourseService()->tryManageCourse($courseId);

		$post = $this->getThreadPostDao()->getPost($id);
		if (empty($post)) {
			throw $this->createServiceException(sprintf('帖子(#%s)不存在，删除失败。', $id));
		}

		if ($post['courseId'] != $courseId) {
			throw $this->createServiceException(sprintf('帖子#%s不属于课程#%s，删除失败。', $id, $courseId));
		}

		$this->getThreadPostDao()->deletePost($post['id']);
		$this->getThreadDao()->waveThread($post['threadId'], 'postNum', -1);
	}

	private function getThreadDao()
	{
		return $this->createDao('Course.ThreadDao');
	}

	private function getThreadPostDao()
	{
		return $this->createDao('Course.ThreadPostDao');
	}

	private function getCourseService()
	{
		return $this->createService('Course.CourseService');
	}

	private function getUserService()
    {
      	return $this->createService('User.UserService');
    }

	private function getNotifiactionService()
    {
      	return $this->createService('User.NotificationService');
    }

    private function getLogService()
    {
    	return $this->createService('System.LogService');
    }

}