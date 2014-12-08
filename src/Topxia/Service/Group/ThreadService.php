<?php

namespace Topxia\Service\Group;

interface ThreadService
{
    public function getThread($id);

    public function isCollected($userId, $threadId);

    public function threadCollect($userId, $threadId);
    
    public function unThreadCollect($userId, $threadId);

    public function searchThreadCollectCount($conditions);

    public function getThreadsByIds($ids);
    
    public function addThread($thread);

    public function updateThread($id,$fields);

    public function closeThread($threadId);

    public function openThread($threadId);

    public function searchThreads($conditions,$orderBy,$start, $limit);
    
    public function searchThreadsCount($conditions);

    public function searchPostsThreadIds($conditions,$orderBy,$start,$limit);
    
    public function searchThreadCollects($conditions,$orderBy,$start,$limit);

    public function searchPostsThreadIdsCount($conditions);

    public function postThread($threadContent,$groupId,$memberId,$threadId,$postId=0);

    public function searchPosts($conditions,$orderBy,$start,$limit);

    public function getPost($id);

    public function searchPostsCount($conditions);

    public function deletePost($postId);

    public function deleteThread($threadId);

    public function setElite($threadId);

    public function removeElite($threadId);

    public function setStick($threadId);

    public function removeStick($threadId);

    public function waveHitNum($threadId);

    public function updatePost($id,$fields);

    public function getTrade($id);

    public function getGoods($id);

    public function addTrade($fields);

    public function getCoinByThreadId($id);

    public function getTradeByUserIdandThreadId($id,$userId);

    public function getTradeByUserIdandGoodsId($userId,$hideId);

    public function addAttach($fileIds,$fileTitles,$fileDescriptions,$fileCoins,$threadId);

    public function addPostAttach($fileIds,$fileTitles,$fileDescriptions,$fileCoins,$threadId,$postId);

    public function searchGoods($conditions,$orderBy,$start,$limit);

    public function deleteGoods($id);

    public function waveGoodsHitNum($goodsId);

}