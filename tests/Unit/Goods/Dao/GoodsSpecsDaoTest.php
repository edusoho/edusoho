<?php

namespace Tests\Unit\Goods\Dao;

use Biz\BaseTestCase;
use Biz\Goods\Dao\GoodsSpecsDao;

class GoodsSpecsDaoTest extends BaseTestCase
{
    public function testGetByGoodsIdAndTargetId()
    {
        $goodsSpecs1 = $this->createGoodsSpecs();
        $goodsSpecs2 = $this->createGoodsSpecs(['goodsId' => $goodsSpecs1['goodsId']]);

        $result = $this->getDao()->getByGoodsIdAndTargetId($goodsSpecs1['goodsId'], $goodsSpecs1['targetId']);

        $this->assertEquals($goodsSpecs1, $result);
    }

    public function testFindByGoodsId()
    {
        $goodSpecs1 = $this->createGoodsSpecs();
        $goodSpecs2 = $this->createGoodsSpecs(['goodsId' => 2]);
        $goodSpecs3 = $this->createGoodsSpecs(['title' => 'testTitle2']);
        $goodSpecs4 = $this->createGoodsSpecs(['title' => 'testTitle2']);

        $result = $this->getDao()->findByGoodsId(1);

        $this->assertEquals([$goodSpecs1, $goodSpecs3, $goodSpecs4], $result);
    }

    public function testDeleteByGoodsIdAndTargetId()
    {
        $goodsSpecs = $this->createGoodsSpecs();

        $before = $this->getDao()->get($goodsSpecs['id']);

        $this->getDao()->deleteByGoodsIdAndTargetId($goodsSpecs['goodsId'], $goodsSpecs['targetId']);

        $after = $this->getDao()->get($goodsSpecs['id']);

        $this->assertEquals($goodsSpecs, $before);
        $this->assertNull($after);
    }

    public function testDeleteByGoodsId()
    {
        $goodSpecs1 = $this->createGoodsSpecs();
        $goodSpecs2 = $this->createGoodsSpecs(['goodsId' => 2]);
        $goodSpecs3 = $this->createGoodsSpecs(['title' => 'testTitle2']);
        $goodSpecs4 = $this->createGoodsSpecs(['title' => 'testTitle2']);

        $beforeCount = $this->getDao()->count(['goodsId' => $goodSpecs1['goodsId']]);
        $beforeTotalCount = $this->getDao()->count([]);

        $this->getDao()->deleteByGoodsId($goodSpecs1['goodsId']);

        $afterCount = $this->getDao()->count(['goodsId' => $goodSpecs1['goodsId']]);
        $afterTotalCount = $this->getDao()->count([]);

        $this->assertEquals(3, $beforeCount);
        $this->assertEquals(4, $beforeTotalCount);
        $this->assertEquals(0, $afterCount);
        $this->assertEquals(1, $afterTotalCount);
    }

    protected function createGoodsSpecs($goodsSpecs = [])
    {
        $default = [
            'goodsId' => 1,
            'targetId' => 1,
            'title' => 'testTitle',
            'images' => [],
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
