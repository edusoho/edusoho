<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\UserLearnDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserLearnDailyDaoImpl extends AdvancedDaoImpl implements UserLearnDailyDao
{
    protected $table = 'user_learn_daily';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'serializes' => [
            ],
            'conditions' => [
                'id = :id',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
