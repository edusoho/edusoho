<?php

namespace Biz\Visualization\Dao\Impl;

use Biz\Visualization\Dao\UserStayDailyDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class UserStayDailyDaoImpl extends AdvancedDaoImpl implements UserStayDailyDao
{
    protected $table = 'user_stay_daily';

    public function sumUserPageStayTime($conditions, $timeField)
    {
        $builder = $this->createQueryBuilder($conditions)
            ->select("userId, sum({$timeField}) as userStayTime")
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
                'userId IN ( :userIds )',
                'userId = :userId',
                'dayTime = :dayTime',
                'createdTime >= :createdTime_GE',
                'createdTime > :createdTime_GT',
                'createdTime <= :createdTime_LE',
                'createdTime < :createdTime_LT',
            ],
            'orderbys' => ['id', 'createdTime'],
        ];
    }
}
