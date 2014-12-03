<?php

namespace Topxia\Service\Group\Dao;

interface ThreadHideDao
{
    public function getHide($id);

    public function addHide($fields);
    
    public function waveHide($id, $field, $diff);

    public function deleteHideByThreadId($id);

    public function getCoinByThreadId($conditions);
}