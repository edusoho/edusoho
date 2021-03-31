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

    public function findByUserIdsAndEventId($userIds, $eventId)
    {
        if (empty($userIds)) {
            return [];
        }
        $marks = str_repeat('?,', count($userIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE eventId = ? AND userId IN ({$marks})";

        return $this->db()->fetchAll($sql, array_merge([$eventId], $userIds)) ?: [];
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
                'id IN (:ids)',
                'eventId = :eventId',
                'eventId IN (:eventIds)',
                'userId = :userId',
                'userId IN ( :userIds )',
                'createdTime >= :startDate',
                'createdTime < :endDate',
            ],
        ];
    }
}
