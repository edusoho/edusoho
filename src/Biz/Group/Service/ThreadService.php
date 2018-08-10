<?php

namespace Biz\Group\Service;

use Biz\System\Annotation\Log;

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

    /**
     * @param $thread
     *
     * @return mixed
     * @Log(level="info",module="group",action="create_thread",message="新增话题",targetType="groups_thread",param="result")
     */
    public function addThread($thread);

    public function updateThread($id, $fields);

    public function closeThread($threadId);

    public function openThread($threadId);

    public function getPost($id);

    public function postThread($threadContent, $groupId, $memberId, $threadId, $postId = 0);

    public function removeStick($threadId);

    public function setStick($threadId);

    public function removeElite($threadId);

    public function setElite($threadId);

    public function addTrade($fields);

    public function addAttach($files, $threadId);

    public function searchGoods($conditions, $orderBy, $start, $limit);

    public function deleteGoods($id);

    public function getTradeByUserIdAndGoodsId($userId, $goodsId);

    public function getGoods($attachId);

    public function waveHitNum($threadId);

    public function sumGoodsCoinsByThreadId($id);

    public function updatePost($id, $post);

    public function searchPosts($conditions, $orderBy, $start, $limit);

    public function searchPostsCount($conditions);

    public function deletePost($id);

    public function addPostAttach($files, $threadId, $postId);

    public function getTradeByUserIdAndThreadId($userId, $threadId);
}
