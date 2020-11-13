<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\UserLearnDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserLearnDailyDaoImpl extends AdvancedDaoImpl implements UserLearnDailyDao
{
    protected $table = 'user_learn_daily';

    public function sumUserLearnTime($conditions, $timeField)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("userId, sum({$timeField}) as userLearnTime")
            ->groupBy('userId');

        return $builder->execute()->fetchAll();
    }

    public function findUserDailyLearnTimeByDate($conditions, $timeField)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("{$timeField} as learnedTime ,from_unixtime(dayTime,'%Y-%m-%d') date");

        return $builder->execute()->fetchAll(0) ?: [];
    }

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
