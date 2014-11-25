<?php

namespace Topxia\Service\Group\Dao;

interface ThreadCollectDao
{
    public function getThreadByFromIdAndToId($userId, $threadId);

    public function addCollect($collectThread);
    
    public function deleteCollect($userId,$threadId);

    public function getCollectThread($id);

    public function searchCollectThreadIdsCount($conditions);

    public function searchCollectThreads($conditions,$orderBy,$start,$limit);

}