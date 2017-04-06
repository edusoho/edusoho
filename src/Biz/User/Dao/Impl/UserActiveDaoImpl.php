<?php

namespace Biz\User\Dao\Impl;

use Biz\User\Dao\UserActiveDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class UserActiveDaoImpl extends GeneralDaoImpl implements UserActiveDao
{
    protected $table = 'user_active_log';

    public function getByUserId($userId)
    {
        $startTime = strtotime(date('Y-m-d', time()));
        $sql = 'SELECT * FROM `user_active_log` WHERE `userId` = ? AND createdTime >= ? LIMIT 1';
        $result = $this->db()->fetchAssoc($sql, array($userId, $startTime));

        return $result ? $result : array();
    }

    public function analysis($startTime, $endTime)
    {
        $sql = 'SELECT DISTINCT `userId` ,`activeTime` as date FROM `user_active_log` WHERE createdTime >= ? AND createdTime <?';

        return $this->db()->fetchAll($sql, array($startTime, $endTime)) ?: array();
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
        );
    }
}
