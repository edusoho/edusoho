<?php

namespace Biz\Live\Dao\Impl;

use Biz\Live\Dao\LiveStatisticsDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class LiveStatisticsDaoImpl extends AdvancedDaoImpl implements LiveStatisticsDao
{
    protected $table = 'live_statistics';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime', 'updatedTime'),
            'orderbys' => array('createdTime'),
            'conditions' => array(
                'liveId = :liveId',
            ),
        );
    }
}