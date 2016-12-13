<?php
namespace Biz\System\Dao;

interface SessionDao
{
    public function getByUserId($userId);

    public function deleteByUserId($userId);

    public function getOnlineCount($retentionTime);

    public function getLoginCount($retentionTime);

    public function deleteByIds($ids);

    public function findBySessionTime($sessionTime, $limit);
}
