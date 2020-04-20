<?php

namespace Biz\S2B2C\Dao\Impl;

use Biz\S2B2C\Dao\ProductDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ProductDaoImpl extends GeneralDaoImpl implements ProductDao
{
    protected $table = 's2b2c_product';

    public function declares()
    {
        return [
           'timestamp' => ['createdTime', 'updatedTime'],
           'serializes' => [],
           'conditions' => [
                'id = :id',
           ],
           'orderbys' => ['id'],
       ];
    }
}
