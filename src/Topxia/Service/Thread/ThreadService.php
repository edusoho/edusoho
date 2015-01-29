<?php
namespace Topxia\Service\Thread;

/**
 * @todo refactor: 去除第一个参数$courseId
 */
interface ThreadService
{
	public function getThread($targetId, $threadId);

	public function findThreadsByType($courseId, $type, $sort = 'latestCreated', $start, $limit);

	public function findLatestThreadsByType($type, $start, $limit);

	public function findEliteThreadsByType($type, $status, $start, $limit);

	public function searchThreads($conditions, $sort, $start, $limit);

	public function searchThreadCount($conditions);

	public function searchThreadCountInCourseIds($conditions);

	public function searchThreadInCourseIds($conditions, $sort, $start, $limit);

	/**
	 * 创建话题
	 */
	public function createThread($thread);

	public function updateThread($targetId, $threadId, $thread);

	public function deleteThread($targetType,$threadId);

	public function stickThread($targetType,$targetId, $threadId);

	public function unstickThread($targetType,$targetId, $threadId);

	public function eliteThread($targetType,$targetId, $threadId);

	public function uneliteThread($targetType,$targetId, $threadId);

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

	public function getPostCountByuserIdAndThreadId($userId,$threadId);

	public function getThreadPostCountByThreadId($threadId);

	/**
	 * 获得话题回帖的数量
	 * @param  integer  $courseId 话题的课程ID
	 * @param  integer  $threadId 话题ID
	 * @return integer  话题回帖的数量
	 */
	public function getThreadPostCount($targetId, $threadId);

	public function findThreadElitePosts($targetId, $threadId, $start, $limit);

	/**
	 * 回复话题
	 **/
	public function getPost($targetId, $id);

	public function createPost($post,$parentId=0);

	public function updatePost($targetId, $id, $fields);

	public function deletePost($targetType,$targetId, $id);

	public function searchPostsCount($conditions);

	public function searchPosts($conditions,$orderBy,$start,$limit);
}