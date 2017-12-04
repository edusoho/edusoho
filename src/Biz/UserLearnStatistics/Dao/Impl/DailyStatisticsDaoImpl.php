<?php

namespace Biz\UserLearnStatistics\Dao\Impl;

use Biz\UserLearnStatistics\Dao\DailyStatisticsDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class DailyStatisticsDaoImpl extends AdvancedDaoImpl implements DailyStatisticsDao
{
    protected $table = 'user_learn_statistics_daily';

    public function findByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }
        
        return $this->findInField('id', $ids);
    }

    public function updateStorageByIds($ids)
    {
        return $this->update(array('ids' => $ids), array('isStorage' => 1));
    }

    public function declares()
    {
        return array(
            'serializes' => array(
            ),
            'orderbys' => array(
                'id',
                'createdTime',
                'updatedTime',
            ),
            'timestamps' => array('createdTime', 'updatedTime'),
            'conditions' => array(
                'id =: id',
                'id IN ( :ids)',
                'id NOT IN ( :excludeIds )',
                'userId IN ( :userIds )',
                'userId = :userId',
                'createdTime >= :createTime_GE',
                'createdTime <= :createTime_LE',
                'updatedTime >= :updateTime_GE',
                'updatedTime <= :updateTime_LE',
                'isStorage = :isStorage',
                'recordTime < :recordTime_LT',
                'recordTime >= :recordTime_GE',
            )
        );
    }
}

