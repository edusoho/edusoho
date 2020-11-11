<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\ActivityLearnDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ActivityLearnDailyDaoImpl extends AdvancedDaoImpl implements ActivityLearnDailyDao
{
    protected $table = 'activity_learn_daily';

    public function findByCourseSetIds($courseSetIds)
    {
        return $this->findInField('courseSetId', $courseSetIds);
    }

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
