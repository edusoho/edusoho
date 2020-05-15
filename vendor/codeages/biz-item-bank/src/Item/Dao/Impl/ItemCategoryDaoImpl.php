<?php

namespace Codeages\Biz\ItemBank\Item\Dao\Impl;

use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;
use Codeages\Biz\ItemBank\Item\Dao\ItemCategoryDao;

class ItemCategoryDaoImpl extends AdvancedDaoImpl implements ItemCategoryDao
{
    protected $table = 'biz_item_category';

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function findByBankId($bankId)
    {
        return $this->findByFields(['bank_id' => $bankId]);
    }

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
                'id IN (:ids)',
            ],
        ];
    }
}
