<?php

namespace Biz\Xapi\Dao\Impl;

use Biz\Xapi\Dao\ActivityWatchLogDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityWatchLogDaoImpl extends AdvancedDaoImpl implements ActivityWatchLogDao
{
    protected $table = 'xapi_activity_watch_log';

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'orderbys' => array(
                'created_time',
            ),
            'serializes' => array(
            ),
            'conditions' => array(
                'id IN ( :ids)',
                'is_push = :is_push',
                'created_time > :created_time_GT',
                'created_time < :created_time_LT',
                'updated_time > :updated_time_GT',
                'updated_time < :updated_time_LT',
            ),
        );
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getLatestWatchLogByUserIdAndActivityId($userId, $activityId, $isPush = 0)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = ? AND activity_id = ? AND is_push = ? ORDER BY created_time DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, array($userId, $activityId, $isPush)) ?: null;
    }
}
