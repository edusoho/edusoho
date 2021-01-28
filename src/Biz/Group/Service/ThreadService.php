<?php

namespace Biz\Group\Service;

use Biz\System\Annotation\Log;

interface ThreadService
{
    public function getThread($id);

    public function searchThreads($conditions, $orderBy, $start, $limit);

    public function countThreads($conditions);

    public function searchPostsThreadIds($conditions, $orderBy, $start, $limit);

    public function countPostsThreadIds($conditions);

    public function getThreadsByIds($ids);

    /**
     * @param $thread
     *
     * @return mixed
     * @Log(module="group",action="create_thread")
     */
    public function addThread($thread);

    /**
     * @param $id
     * @param $fields
     *
     * @return mixed
     * @Log(module="group",action="update_thread",param="id")
     */
    public function updateThread($id, $fields);

    /**
     * @param $threadId
     *
     * @return mixed
     * @Log(module="group",action="close_thread",funcName="getThread")
     */
    public function closeThread($threadId);

    /**
     * @param $threadId
     *
     * @return mixed
     * @Log(module="group",action="open_thread",funcName="getThread")
     */
    public function openThread($threadId);

    /**
     * @param $threadId
     *
     * @return mixed
     * @Log(module="group",action="delete_thread")
     */
    public function deleteThread($threadId);

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
