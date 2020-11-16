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
                'dayTime = :dayTime',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
