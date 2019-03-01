<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\LogDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LogDaoImpl extends GeneralDaoImpl implements LogDao
{
    protected $table = 'log_v8';

    public function declares()
    {
        return array(
            'orderbys' => array(
                'createdTime',
                'id',
            ),
            'conditions' => array(
                'module = :module',
                'module IN (:modules)',
                'action = :action',
                'action IN ( :actions )',
                'action NOT IN ( :excludeActions )',
                'level = :level',
                'userId = :userId',
                'createdTime > :startDateTime',
                'createdTime < :endDateTime',
                'createdTime >= :startDateTime_GE',
                'userId IN ( :userIds )',
                'userId != :exceptedUserId',
            ),
        );
    }

    public function analysisLoginNumByTime($startTime, $endTime)
    {
        $sql = "SELECT count(distinct userid)  as num FROM `{$this->table}` WHERE `action`='login_success' AND  `createdTime`>= ? AND `createdTime`<= ?  ";

        return $this->db()->fetchColumn($sql, array($startTime, $endTime));
    }

    public function analysisLoginDataByTime($startTime, $endTime)
    {
        $sql = "SELECT count(distinct userid) as count, from_unixtime(createdTime,'%Y-%m-%d') as date FROM `{$this->table}` WHERE `action`='login_success' AND `createdTime`>= ? AND `createdTime`<= ? group by from_unixtime(`createdTime`,'%Y-%m-%d') order by date ASC ";

        return $this->db()->fetchAll($sql, array($startTime, $endTime));
    }
}
