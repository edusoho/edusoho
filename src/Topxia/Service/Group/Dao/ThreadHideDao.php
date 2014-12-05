<?php

namespace Topxia\Service\Group\Dao;

interface ThreadHideDao
{
    public function getHide($id);

    public function addHide($fields);

    public function updateHide($id,$fields);    
    
    public function waveHide($id, $field, $diff);

    public function deleteHideByThreadId($id,$type);

    public function getCoinByThreadId($conditions);

    public function searchHides($conditions,$orderBy,$start,$limit);
}