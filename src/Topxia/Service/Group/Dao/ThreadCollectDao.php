<?php

namespace Topxia\Service\Group\Dao;

interface ThreadCollectDao
{
    public function getThreadByUserIdAndThreadId($userId, $threadId);

    public function addThreadCollect($collectThread);
    
    public function deleteThreadCollectByUserIdAndThreadId($userId,$threadId);

    public function getThreadCollect($id);

    public function searchThreadCollectCount($conditions);

    public function searchThreadCollects($conditions,$orderBy,$start,$limit);

}