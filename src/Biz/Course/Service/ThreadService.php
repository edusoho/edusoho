<?php

namespace Biz\Course\Service;

use Biz\System\Annotation\Log;

// TODO refactor. use Thread.
interface ThreadService
{
    public function searchThreads($conditions, $sort, $start, $limit);

    public function countThreads($conditions);

    public function getThreadByThreadId($threadId);

    public function getThread($courseId, $threadId);

    public function findThreadsByType($courseId, $type, $sort, $start, $limit);

    public function findLatestThreadsByType($type, $start, $limit);

    public function findEliteThreadsByType($type, $status, $start, $limit);

    public function searchThreadCountInCourseIds($conditions);

    public function searchThreadInCourseIds($conditions, $sort, $start, $limit);

    /**
     * @param $thread
     *
     * @return mixed
     * @Log(module="course",action="create_thread")
     */
    public function createThread($thread);

    /**
     * @param $courseId
     * @param $threadId
     * @param $thread
     *
     * @return mixed
     * @Log(module="course",action="update_thread",funcName="getThreadByThreadId",param="threadId")
     */
    public function updateThread($courseId, $threadId, $thread);

    /**
     * @param $threadId
     *
     * @return mixed
     * @Log(module="course",action="delete_thread",funcName="getThreadByThreadId")
     */
    public function deleteThread($threadId);

    public function stickThread($courseId, $threadId);

    public function unstickThread($courseId, $threadId);

    public function eliteThread($courseId, $threadId);

    public function uneliteThread($courseId, $threadId);

    /**
     * 点击查看话题.
     *
     * 此方法，用来增加话题的查看数。
     *
     * @param int $courseId 课程ID
     * @param int $threadId 话题ID
     */
    public function hitThread($courseId, $threadId);

    /**
     * 获得话题的回帖.
     *
     * @param int    $courseId 话题的课程ID
     * @param int    $threadId 话题ID
     * @param string $sort     排序方式： defalut按帖子的发表时间顺序；best按顶的次序排序
     * @param int    $start    开始行数
     * @param int    $limit    获取数据的限制行数
     *
     * @return array 获得的话题回帖列表
     */
    public function findThreadPosts($courseId, $threadId, $sort, $start, $limit);

    public function searchThreadPosts($conditions, $sort, $start, $limit);

    public function searchThreadPostsCount($conditions);

    public function getPostCountByuserIdAndThreadId($userId, $threadId);

    public function getThreadPostCountByThreadId($threadId);

    /**
     * 获取我回复的帖子数量
     *
     * @return int
     */
    public function getMyReplyThreadCount();

    public function getMyLatestReplyPerThread($start, $limit);

    /**
     * 获得话题回帖的数量.
     *
     * @param int $courseId 话题的课程ID
     * @param int $threadId 话题ID
     *
     * @return int 话题回帖的数量
     */
    public function getThreadPostCount($courseId, $threadId);

    public function findThreadElitePosts($courseId, $threadId, $start, $limit);

    /**
     * 回复话题.
     */
    public function getPost($courseId, $id);

    public function createPost($post);

    public function postAtNotifyEvent($post, $users);

    public function updatePost($courseId, $id, $fields);

    public function deletePost($courseId, $id);

    public function countPartakeThreadsByUserId($userId);

    public function findThreadIds($conditions);

    public function findPostThreadIds($conditions);
}
