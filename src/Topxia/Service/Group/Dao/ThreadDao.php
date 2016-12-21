<?php

namespace Topxia\Service\Group\Dao;

interface ThreadDao
{
    public function getThreadsByIds($ids);
    
    public function addThread($thread);

    public function waveThread($id, $field, $diff);

    public function updateThread($id,$fields);

    public function deleteThread($id);
}