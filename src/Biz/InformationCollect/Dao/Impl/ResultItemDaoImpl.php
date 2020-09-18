<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\ResultItemDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ResultItemDaoImpl extends AdvancedDaoImpl implements ResultItemDao
{
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
            ],
        ];
    }
}
