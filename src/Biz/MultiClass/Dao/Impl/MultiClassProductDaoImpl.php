<?php

namespace Biz\MultiClass\Dao\Impl;

use Biz\MultiClass\Dao\MultiClassProductDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class MultiClassProductDaoImpl extends AdvancedDaoImpl implements MultiClassProductDao
{
    protected $table = 'multi_class_product';

    public function getByTitle($title)
    {
        return $this->getByFields(['title' => $title]);
    }

    public function findByIds($ids)
    {
        return $this->findInField('id', $ids);
    }

    public function getByType($type)
    {
        return $this->getByFields(['type' => $type]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'orderbys' => ['id', 'createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'title = :title',
                'title LIKE :keywords',
            ],
        ];
    }
}
