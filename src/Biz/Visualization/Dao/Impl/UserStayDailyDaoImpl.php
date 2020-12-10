<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\UserStayDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserStayDailyDaoImpl extends AdvancedDaoImpl implements UserStayDailyDao
{
    protected $table = 'user_stay_daily';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
                'userId IN ( :userIds )',
                'userId = :userId',
                'dayTime = :dayTime',
                'dayTime >= :dayTime_GE',
                'dayTime > :dayTime_GT',
                'dayTime <= :dayTime_LE',
                'dayTime < :dayTime_LT',
            ],
            'orderbys' => ['id', 'createdTime', 'dayTime'],
        ];
    }
}
