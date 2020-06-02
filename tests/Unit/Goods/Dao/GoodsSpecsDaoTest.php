<?php

namespace Tests\Unit\Goods\Dao;

use Biz\BaseTestCase;
use Biz\Goods\Dao\GoodsSpecsDao;

class GoodsSpecsDaoTest extends BaseTestCase
{
    public function testFindBygoodsId()
    {
        $goodSpecs1 = $this->createGoodsSpecs();
        $goodSpecs2 = $this->createGoodsSpecs(['goodsId' => 2]);
        $goodSpecs3 = $this->createGoodsSpecs(['title' => 'testTitle2']);
        $goodSpecs4 = $this->createGoodsSpecs(['title' => 'testTitle2']);

        $result = $this->getDao()->findByGoodsId(1);

        $this->assertArrayEquals([$goodSpecs1, $goodSpecs3, $goodSpecs4], $result);
    }

    protected function createGoodsSpecs($goodsSpecs = [])
    {
        $default = [
            'goodsId' => 1,
            'title' => 'testTitle',
            'images' => [],
            'periodType' => 'test type',
            'authority' => [],
        ];

        $goodsSpecs = array_merge($default, $goodsSpecs);

        return $this->getDao()->create($goodsSpecs);
    }

    /**
     * @return GoodsSpecsDao
     */
    protected function getDao()
    {
        return $this->createDao('Goods:GoodsSpecsDao');
    }
}
