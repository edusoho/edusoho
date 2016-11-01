<?php
/**
 * User: Edusoho V8
 * Date: 19/10/2016
 * Time: 19:08
 */

namespace Topxia\Service\User\Dao\Impl;


use Topxia\Service\Common\BaseDao;
use Topxia\Service\User\Dao\UserActiveDao;

class UserActiveDaoImpl extends BaseDao implements UserActiveDao
{
    protected $table = 'user_active_log';

    public function createActiveUser($activeUser)
    {
        $activeUser['createdTime'] = time();
        $this->getConnection()->insert($this->getTable(), $activeUser);
    }

    public function getActiveUser($userId)
    {
        $that = $this;
        $startTime = strtotime(date('Y-m-d', time()));

        return $this->fetchCached("userId:{$userId}:startTime:{$startTime}", $userId, $startTime, function ($userId, $startTime) use ($that) {
            $sql       = "SELECT * FROM `user_active_log` WHERE `userId` = ? AND createdTime >= ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($userId, $startTime));
        });

    }

    public function analysisActiveUser($startTime, $endTime)
    {
        $sql = "SELECT DISTINCT `userId` ,`activeTime` as date FROM `user_active_log` WHERE createdTime >= ? AND createdTime <?";
        return $this->getConnection()->fetchAll($sql, array($startTime, $endTime)) ?: array();
    }

}