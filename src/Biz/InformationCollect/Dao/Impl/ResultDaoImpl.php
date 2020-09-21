<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\ResultDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ResultDaoImpl extends GeneralDaoImpl implements ResultDao
{
    protected $table = 'infomation_collect_result';

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
                'submitter = :submitter',
                'eventId = :eventId',
            ],
        ];
    }
}
