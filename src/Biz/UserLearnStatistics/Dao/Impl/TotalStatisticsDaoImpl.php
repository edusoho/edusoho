<?php

namespace Biz\UserLearnStatistics\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Biz\UserLearnStatistics\Dao\TotalStatisticsDao;

class TotalStatisticsDaoImpl extends AdvancedDaoImpl implements TotalStatisticsDao
{
    protected $table = 'user_learn_statistics_total';

    public function findByIds($ids)
    {
        if (empty($ids)) {
            return array();
        }
        
        return $this->findInField('id', $ids);
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
                'createdTime >= :createTime_GE',
                'createdTime <= :createTime_LE',
                'updatedTime >= :updateTime_GE',
                'updatedTime <= :updateTime_LE',
            )
        );
    }
}

