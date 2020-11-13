<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\CoursePlanVideoDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CoursePlanVideoDailyDaoImpl extends AdvancedDaoImpl implements CoursePlanVideoDailyDao
{
    protected $table = 'course_plan_video_daily';

    public function sumUserVideoWatchTime($conditions, $timeField)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("userId, sum({$timeField}) as userVideoTime")
            ->groupBy('userId');

        return $builder->execute()->fetchAll();
    }

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
