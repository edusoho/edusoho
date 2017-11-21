<?php

namespace Biz\LearnStatistics\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

class LearnStatisticsDaoImpl extends \Codeages\Biz\Framework\Dao\GeneralDaoImpl
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
                'create_time',
                'update_time',
            ),
            'timestamps' => array('create_time', 'update_time'),
            'conditions' => array(
                'id =: id',
                'id IN ( :ids)',
                'id NOT IN ( :excludeIds )',
                'create_time >= :createTime_GE',
                'create_time <= :createTime_LE',
                'update_time >= :updateTime_GE',
                'update_time <= :updateTime_LE',
            )
        );
    }
}

