<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\ResultDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ResultDaoImpl extends AdvancedDaoImpl implements ResultDao
{
    protected $table = 'information_collect_result';

    public function getByUserIdAndEventId($userId, $eventId)
    {
        return $this->getByFields(['userId' => $userId, 'eventId' => $eventId]) ?: null;
    }

    public function countGroupByEventId($eventIds)
    {
        $builder = $this->createQueryBuilder(['eventIds' => $eventIds])
            ->select('eventId, count(id) AS collectNum')
            ->groupBy('eventId');

        return $builder->execute()->fetchAll();
    }

    public function declares()
    {
        return [
            'serializes' => [
            ],
            'orderbys' => [
                'id', 'createdTime',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'id = :id',
                'eventId IN (:eventIds)',
                'userId = :userId',
                'eventId = :eventId',
                'createdTime >= :startDate',
                'createdTime < :endDate',
            ],
        ];
    }
}
