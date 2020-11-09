<?php

namespace Biz\Visualization\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityStayDailyDaoImpl extends AdvancedDaoImpl
{
    protected $table = 'activity_stay_daily';

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
