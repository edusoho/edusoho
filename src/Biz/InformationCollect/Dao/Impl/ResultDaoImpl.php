<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\ResultDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ResultDaoImpl extends AdvancedDaoImpl implements ResultDao
{
    protected $table = 'information_collect_result';

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
                'id',
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'conditions' => [
                'id = :id',
                'eventId IN (:eventIds)',
                'submitter = :submitter',
                'eventId = :eventId',
            ],
        ];
    }
}
