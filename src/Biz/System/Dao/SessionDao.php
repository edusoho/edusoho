<?php

namespace Biz\System\Dao;

interface SessionDao
{
    public function getByUserId($userId);

    public function deleteByUserId($userId);

    public function countOnline($retentionTime);

    public function countLogin($retentionTime);

    public function deleteByIds($ids);

    public function searchBySessionTime($sessionTime, $limit);
}
