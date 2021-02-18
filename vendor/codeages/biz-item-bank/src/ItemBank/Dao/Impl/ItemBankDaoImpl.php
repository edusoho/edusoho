<?php

namespace Codeages\Biz\ItemBank\ItemBank\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\Biz\ItemBank\ItemBank\Dao\ItemBankDao;

class ItemBankDaoImpl extends GeneralDaoImpl implements ItemBankDao
{
    protected $table = 'biz_item_bank';

    public function declares()
    {
        return [
            'timestamps' => [
                'created_time',
                'updated_time',
            ],
            'orderbys' => [
                'id',
                'created_time',
                'updated_time',
            ],
            'conditions' => [
                'id = :id',
                'name like :nameLike',
            ],
        ];
    }
}
