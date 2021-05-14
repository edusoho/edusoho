<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassProductDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class MultiClassProductDaoImpl extends GeneralDaoImpl implements MultiClassProductDao
{
    protected $table = 'multi_class_product';

    public function getProductByTitle($title)
    {
        return $this->getByFields(['title' => $title]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'title = :title',
            ],
        ];
    }
}
