<?php

namespace Topxia\Service\Group\Dao;

interface ThreadDao
{
	
    public function getThread($id);

    public function getThreadsByIds($ids);
    
    public function searchThreads($conditions,$orderBy,$start, $limit);

    public function searchThreadsCount($conditions);

    public function addThread($thread); 

    public function waveThread($id, $field, $diff);

    public function updateThread($id,$fields);

    public function deleteThread($id);
}