<?php

namespace Biz\InformationCollect\Dao\Impl;

use Biz\InformationCollect\Dao\ItemDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class ItemDaoImpl extends AdvancedDaoImpl implements ItemDao
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
            ],
            'conditions' => [
                'id = :id',
            ],
        ];
    }
}
