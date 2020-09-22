<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\ResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ResultDaoImpl extends GeneralDaoImpl implements ResultDao
{
    protected $table = 'information_collect_result';

    public function getByUserIdAndEventId($userId, $eventId)
    {
        return $this->getByFields(['userId' => $userId, 'eventId' => $eventId]) ?: [];
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
            ],
        ];
    }
}
