<?php
namespace Topxia\Service\Thread;

/**
 * @todo refactor: 去除第一个参数$courseId
 */
interface ThreadService
{
	public function getThread($threadId);

	public function searchThreads($conditions, $sort, $start, $limit);

	public function searchThreadCount($conditions);

	public function findThreadsByTargetAndUserId($target, $userId, $start, $limit);

	public function findZeroPostThreadsByTarget($target, $start, $limit);

	/**
	 * 创建话题
	 */
	public function createThread($thread);

	public function updateThread($id, $fields);

	public function deleteThread($threadId);

	public function setThreadSticky($threadId);

	public function cancelThreadSticky($threadId);

	public function setThreadNice($threadId);

	public function cancelThreadNice($threadId);

	/**
	 * 点击查看话题
	 *
	 * 此方法，用来增加话题的查看数。
	 * 
	 * @param integer $courseId 课程ID
	 * @param integer $threadId 话题ID
	 * 
	 */
	public function hitThread($targetId, $threadId);

	/**
	 * 获得话题的回帖
	 * 
	 * @param integer  $courseId 话题的课程ID
	 * @param integer  $threadId 话题ID
	 * @param string  	$sort     排序方式： defalut按帖子的发表时间顺序；best按顶的次序排序。
	 * @param integer 	$start    开始行数
	 * @param integer 	$limit    获取数据的限制行数
	 * 
	 * @return array 获得的话题回帖列表。
	 */
	public function findThreadPosts($targetId, $threadId, $sort = 'default', $start, $limit);

	/**
	 * 获得话题回帖的数量
	 * @param  integer  $courseId 话题的课程ID
	 * @param  integer  $threadId 话题ID
	 * @return integer  话题回帖的数量
	 */
	public function getThreadPostCount($targetId, $threadId);

	/**
	 * 回复话题
	 **/
	public function getPost($id);

	public function getPostPostionInThread($id);

	public function findPostsByParentId($parentId, $start, $limit);

	public function findPostsCountByParentId($parentId);

	public function createPost($fields);

	public function deletePost($postId);

	public function searchPostsCount($conditions);

	public function searchPosts($conditions, $orderBy, $start, $limit);

	public function voteUpPost($id);

}