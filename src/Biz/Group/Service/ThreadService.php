<?php
namespace Biz\Group\Service;

// TODO refactor. use Thread.
interface ThreadService
{
    public function getThread($id);

    public function searchThreads($conditions, $orderBy, $start, $limit);

    public function countThreads($conditions);

    public function searchThreadCollects($conditions, $orderBy, $start, $limit);

    public function countThreadCollects($conditions);

    public function searchPostsThreadIds($conditions, $orderBy, $start, $limit);

    public function countPostsThreadIds($conditions);

    public function isCollected($userId, $threadId);

    public function threadCollect($userId, $threadId);

    public function unThreadCollect($userId, $threadId);

    public function getThreadsByIds($ids);

    public function addThread($thread);

    public function updateThread($id, $fields);

    public function closeThread($threadId);

    public function openThread($threadId);
}
