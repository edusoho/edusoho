<?php

namespace Tests\Unit\Goods\Dao;

use Biz\BaseTestCase;
use Biz\Goods\Dao\GoodsDao;

class GoodsDaoTest extends BaseTestCase
{
    public function testGetByProductId()
    {
        $goods = $this->createGoods();
        $goods2 = $this->createGoods(['productId' => $goods['productId'] + 1]);

        $result = $this->getDao()->getByProductId($goods['productId']);
        $this->assertEquals($goods, $result);
    }

    protected function createGoods($goods = [])
    {
        $default = [
            'productId' => 1,
            'title' => 'testTitle',
            'images' => [],
        ];

        $goods = array_merge($default, $goods);

        return $this->getDao()->create($goods);
    }

    /**
     * @return GoodsDao
     */
    protected function getDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }
}
