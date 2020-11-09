<?php

namespace Biz\Visualization\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityVideoDailyDaoImpl extends AdvancedDaoImpl
{
    protected $table = 'activity_video_daily';

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
