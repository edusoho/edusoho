<?php

namespace Tests\Unit\Goods\Service;

use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\Goods\Dao\GoodsDao;
use Biz\Goods\Service\GoodsService;

class GoodsServiceTest extends BaseTestCase
{
    public function testCreateGoods_whenMissingProductId_thenThrowParamMissingException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $this->getGoodsService()->createGoods(['title' => 'testTitle']);
    }

    public function testCreateGoods_whenMissingTitle_thenThrowParamMissingException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $this->getGoodsService()->createGoods(['productId' => 1]);
    }

    public function testCreateGoods()
    {
        $result = $this->getGoodsService()->createGoods([
            'productId' => 1,
            'title' => 'testTitle',
            'images' => ['testImages'],
        ]);

        $this->assertEquals(1, $result['productId']);
        $this->assertEquals('testTitle', $result['title']);
        $this->assertArrayEquals(['testImages'], $result['images']);
    }

    public function testGetGoods()
    {
        $expected = $this->createGoods();

        $result = $this->getGoodsService()->getGoods($expected['id']);

        $this->assertArrayEquals($expected, $result);
    }

    public function testUpdateGoods()
    {
        $goods = $this->createGoods();

        $fields = [
            'productId' => 2,
            'title' => 'test update title',
            'images' => ['test update images'],
        ];

        $result = $this->getGoodsService()->updateGoods($goods['id'], $fields);
        $this->assertEquals($goods['productId'], $result['productId']);
        $this->assertNotEquals($goods['title'], $result['title']);
        $this->assertArrayEquals($fields['images'], $result['images']);
    }

    public function testDeleteGoods()
    {
        $goods = $this->createGoods();
        $before = $this->getGoodsDao()->get($goods['id']);
        $this->assertNotEmpty($before);

        $this->getGoodsService()->deleteGoods($goods['id']);

        $after = $this->getGoodsDao()->get($goods['id']);
        $this->assertEmpty($after);
    }

    public function testSearchGoods_withDifferentConditions()
    {
        $goods1 = $this->createGoods();
        $goods2 = $this->createGoods(['title' => 'testTitle2']);
        $goods3 = $this->createGoods(['productId' => 2]);
        $goods4 = $this->createGoods(['productId' => 2]);

        $conditions1 = ['titleLike' => 'test'];

        $result1 = $this->getGoodsService()->searchGoods($conditions1, ['id' => 'ASC'], 0, 10);

        $this->assertArrayEquals([$goods1, $goods2, $goods3, $goods4], $result1);

        $conditions2 = ['productId' => 2];
        $result2 = $this->getGoodsService()->searchGoods($conditions2, ['id' => 'ASC'], 0, 10);

        $this->assertArrayEquals([$goods3, $goods4], $result2);

        $conditions3 = ['title' => 'testTitle2'];
        $result3 = $this->getGoodsService()->searchGoods($conditions3, ['id' => 'ASC'], 0, 10);

        $this->assertArrayEquals([$goods2], $result3);
    }

    public function testSearchGoods_withDifferentOrderBysAndLimits()
    {
        $goods1 = $this->createGoods();
        $goods2 = $this->createGoods(['title' => 'testTitle2']);
        $goods3 = $this->createGoods(['productId' => 2]);
        $goods4 = $this->createGoods(['productId' => 2]);

        $result1 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'ASC'], 0, 10);
        $this->assertArrayEquals([$goods1, $goods3, $goods4], $result1);

        $result2 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'DESC'], 0, 10);
        $this->assertArrayEquals([$goods4, $goods3, $goods1], $result2);

        $result3 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'ASC'], 0, 2);
        $this->assertArrayEquals([$goods1, $goods3], $result3);
    }

    public function testSearchGoods_withDifferentColumns()
    {
        $goods1 = $this->createGoods();
        $goods2 = $this->createGoods(['title' => 'testTitle2']);
        $goods3 = $this->createGoods(['productId' => 2]);
        $goods4 = $this->createGoods(['productId' => 2]);

        $expected1 = [
            ['productId' => $goods1['productId'], 'title' => $goods1['title']],
            ['productId' => $goods3['productId'], 'title' => $goods3['title']],
            ['productId' => $goods4['productId'], 'title' => $goods4['title']],
        ];

        $result1 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'ASC'], 0, 10, ['productId', 'title']);
        $this->assertArrayEquals($expected1, $result1);

        $expected2 = [
            ['title' => $goods1['title']],
            ['title' => $goods3['title']],
            ['title' => $goods4['title']],
        ];

        $result2 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'ASC'], 0, 10, ['title']);
        $this->assertArrayEquals($expected2, $result2);
    }

    protected function createGoods($goods = [])
    {
        $default = [
            'productId' => 1,
            'title' => 'testTitle',
            'images' => [],
        ];

        $goods = array_merge($default, $goods);

        return $this->getGoodsDao()->create($goods);
    }

    /**
     * @return GoodsDao
     */
    protected function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
