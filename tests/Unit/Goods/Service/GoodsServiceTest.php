<?php

namespace Tests\Unit\Goods\Service;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Common\CommonException;
use Biz\Goods\Dao\GoodsDao;
use Biz\Goods\Dao\GoodsSpecsDao;
use Biz\Goods\GoodsException;
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
            'type' => 'course',
            'images' => ['testImages'],
        ]);

        $this->assertEquals(1, $result['productId']);
        $this->assertEquals('testTitle', $result['title']);
        $this->assertEquals(['testImages'], $result['images']);
    }

    public function testGetGoods()
    {
        $expected = $this->createGoods();

        $result = $this->getGoodsService()->getGoods($expected['id']);

        $this->assertEquals($expected, $result);

        $result2 = $this->getGoodsService()->getGoods($expected['id'] + 10000);

        $this->assertNull($result2);
    }

    public function testUpdateGoods()
    {
        $goods = $this->createGoods();

        $fields = [
            'productId' => 2,
            'title' => 'test update title',
            'type' => 'course',
            'images' => ['test update images'],
        ];

        $result = $this->getGoodsService()->updateGoods($goods['id'], $fields);
        $this->assertEquals($goods['productId'], $result['productId']);
        $this->assertNotEquals($goods['title'], $result['title']);
        $this->assertEquals($fields['images'], $result['images']);
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

        $this->assertEquals([$goods1, $goods2, $goods3, $goods4], $result1);

        $conditions2 = ['productId' => 2];
        $result2 = $this->getGoodsService()->searchGoods($conditions2, ['id' => 'ASC'], 0, 10);

        $this->assertEquals([$goods3, $goods4], $result2);

        $conditions3 = ['title' => 'testTitle2'];
        $result3 = $this->getGoodsService()->searchGoods($conditions3, ['id' => 'ASC'], 0, 10);

        $this->assertEquals([$goods2], $result3);
    }

    public function testSearchGoods_withDifferentOrderBysAndLimits()
    {
        $goods1 = $this->createGoods();
        $goods2 = $this->createGoods(['title' => 'testTitle2']);
        $goods3 = $this->createGoods(['productId' => 2]);
        $goods4 = $this->createGoods(['productId' => 2]);

        $result1 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'ASC'], 0, 10);
        $this->assertEquals([$goods1, $goods3, $goods4], $result1);

        $result2 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'DESC'], 0, 10);
        $this->assertEquals([$goods4, $goods3, $goods1], $result2);

        $result3 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'ASC'], 0, 2);
        $this->assertEquals([$goods1, $goods3], $result3);
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
        $this->assertEquals($expected1, $result1);

        $expected2 = [
            ['title' => $goods1['title']],
            ['title' => $goods3['title']],
            ['title' => $goods4['title']],
        ];

        $result2 = $this->getGoodsService()->searchGoods(['title' => 'testTitle'], ['id' => 'ASC'], 0, 10, ['title']);
        $this->assertEquals($expected2, $result2);
    }

    public function testGetGoodsByProductId()
    {
        $goods = $this->createGoods();
        $goods2 = $this->createGoods(['productId' => $goods['productId'] + 1000]);

        $result = $this->getGoodsService()->getGoodsByProductId($goods['productId']);

        $this->assertEquals($goods, $result);
    }

    public function testCreateGoodsSpecs_whenMissingGoodsId_thenThrowParamMissingException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $this->getGoodsService()->createGoodsSpecs(['targetId' => 1, 'title' => 'testTitle']);
    }

    public function testCreateGoodsSpecs_whenMissingTargetId_thenThrowParamMissingException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $this->getGoodsService()->createGoodsSpecs(['goodsId' => 1, 'title' => 'testTitle']);
    }

    public function testCreateGoodsSpecs_whenMissingTitle_thenThrowParamMissingException()
    {
        $this->expectException(CommonException::class);
        $this->expectExceptionMessage('exception.common_parameter_missing');
        $this->getGoodsService()->createGoodsSpecs(['goodsId' => 1, 'targetId' => 2]);
    }

    public function testCreateGoodsSpecs()
    {
        $result = $this->getGoodsService()->createGoodsSpecs(['goodsId' => 1, 'targetId' => 2, 'title' => 'test title']);

        $this->assertEquals('1', $result['id']);
        $this->assertEquals('1', $result['goodsId']);
        $this->assertEquals('2', $result['targetId']);
        $this->assertEquals('test title', $result['title']);
        $this->assertEmpty($result['images']);
        $this->assertEquals('0.00', $result['price']);
    }

    public function testGetGoodsSpecs()
    {
        $expected = $this->createGoodsSpecs();

        $result = $this->getGoodsService()->getGoodsSpecs($expected['id']);

        $this->assertEquals($expected, $result);

        $result2 = $this->getGoodsService()->getGoodsSpecs($expected['id'] + 10000);

        $this->assertNull($result2);
    }

    public function testUpdateGoodsSpecs()
    {
        $goodsSpecs = $this->createGoodsSpecs();
        $before = $this->getGoodsSpecsDao()->get($goodsSpecs['id']);

        $fields = [
            'goodsId' => $goodsSpecs['goodsId'] + 2,
            'targetId' => $goodsSpecs['targetId'] + 4,
            'title' => 'test update title',
            'images' => ['testImg'],
            'price' => '4.00',
        ];

        $this->getGoodsService()->updateGoodsSpecs($goodsSpecs['id'], $fields);

        $after = $this->getGoodsSpecsDao()->get($goodsSpecs['id']);

        $this->assertEquals($before['goodsId'], $after['goodsId']);
        $this->assertEquals($before['targetId'], $after['targetId']);
        $this->assertEquals($fields['title'], $after['title']);
        $this->assertEquals($fields['images'], $after['images']);
        $this->assertEquals($fields['price'], $after['price']);
        $this->assertNotEquals($before['title'], $after['title']);
        $this->assertNotEquals($before['images'], $after['images']);
        $this->assertNotEquals($before['price'], $after['price']);
    }

    public function testDeleteGoodsSpecs()
    {
        $goodsSpecs = $this->createGoodsSpecs();
        $before = $this->getGoodsSpecsDao()->get($goodsSpecs['id']);

        $this->getGoodsService()->deleteGoodsSpecs($goodsSpecs['id']);

        $after = $this->getGoodsSpecsDao()->get($goodsSpecs['id']);

        $this->assertEquals($goodsSpecs, $before);
        $this->assertEmpty($after);
    }

    public function testGetGoodsSpecsByGoodsIdAndTargetId()
    {
        $goodsSpecs = $this->createGoodsSpecs();
        $goodsSpecs2 = $this->createGoodsSpecs(['goodsId' => $goodsSpecs['goodsId'] + 1000]);

        $result = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goodsSpecs['goodsId'], $goodsSpecs['targetId']);

        $this->assertEquals($goodsSpecs, $result);
    }

    public function testFindGoodSpecsByGoodsId()
    {
        $goods = $this->createGoods();

        $goodsSpecs1 = $this->createGoodsSpecs(['goodsId' => $goods['id']]);
        $goodsSpecs2 = $this->createGoodsSpecs(['goodsId' => $goods['id'] + 1]);
        $goodsSpecs3 = $this->createGoodsSpecs(['goodsId' => $goods['id'] + 1]);
        $goodsSpecs4 = $this->createGoodsSpecs(['goodsId' => $goods['id']]);

        $result = $this->getGoodsService()->findGoodsSpecsByGoodsId($goods['id']);
        $result = ArrayToolkit::index($result, 'id');

        $expected = [
            $goodsSpecs1['id'] => $goodsSpecs1,
            $goodsSpecs4['id'] => $goodsSpecs4,
        ];

        $this->assertEquals(count($expected), count($result));
        $this->assertEmpty(array_diff(array_keys($expected), array_keys($result)));
        $this->assertEquals($goodsSpecs4, $result[$goodsSpecs4['id']]);
    }

    public function testGetGoodsSpecsByProductIdAndTargetId_whenGoodsNotExist_thenThrowException()
    {
        $goods = $this->createGoods();

        $this->expectException(GoodsException::class);
        $this->expectExceptionMessage('exception.goods.not_found');

        $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($goods['productId'] + 1000, 1);
    }

    public function testGetGoodsSpecsByProductIdAndTargetId()
    {
        $goods = $this->createGoods();
        $goodsSpecs = $this->createGoodsSpecs([
            'goodsId' => $goods['id'],
            'targetId' => 1,
            'title' => $goods['title'],
        ]);

        $goodsSpecs2 = $this->createGoodsSpecs([
            'goodsId' => $goods['id'],
            'targetId' => 2,
            'title' => $goods['title'],
        ]);

        $result = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($goods['productId'], 1);
        $this->assertEquals($goodsSpecs, $result);

        $resultNull = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($goods['productId'], 1000);
        $this->assertNull($resultNull);
    }

    /**
     * 权限判断：教师身份并且是当前商品的Creator返回True
     */
    public function testCanManageGoods_whenTeacherRoleAndEqualCreator_thenTrue()
    {
        $this->loadCurrentUserWithTeacher(['id' => 5]);
        $goods = [
            'creator' => 5,
        ];
        $this->assertTrue(ReflectionUtils::invokeMethod($this->getGoodsService(), 'canManageGoods', [$goods]));
    }

    /**
     * 权限判断： 教师身份，不过不是当前商品Creator返回False
     */
    public function testCanManageGoods_whenTeacherRoleAndNotEqualCreator_thenFalse()
    {
        $this->loadCurrentUserWithTeacher(['id' => 5]);
        $goods = [
            'creator' => 10,
        ];
        $this->assertFalse(ReflectionUtils::invokeMethod($this->getGoodsService(), 'canManageGoods', [$goods]));
    }

    /**
     * 权限判断： 管理员身份，可以有权限管理
     */
    public function testCanManageGoods_whenAdminRole_thenTrue()
    {
        $this->loadCurrentUserWithAdmin();
        $goods = [
            'creator' => 10,
        ];
        $this->assertTrue(ReflectionUtils::invokeMethod($this->getGoodsService(), 'canManageGoods', [$goods]));
    }

    /**
     * 权限判断： 普通用户权限，没有管理权限
     */
    public function testCanManageGoods_whenNormalUserRole_thenFalse()
    {
        $this->loadCurrentUserWithNormalUser();
        $goods = [
            'creator' => 10,
        ];
        $this->assertFalse(ReflectionUtils::invokeMethod($this->getGoodsService(), 'canManageGoods', [$goods]));
    }

    public function testFindGoodsByIds()
    {
        $goods1 = $this->createGoods();
        $goods2 = $this->createGoods(['title' => 'testTitle2']);
        $goods3 = $this->createGoods(['productId' => 2]);
        $goods4 = $this->createGoods(['productId' => 2]);

        $result = $this->getGoodsService()->findGoodsByIds([$goods1['id'], $goods2['id']]);
        $this->assertEquals([
            $goods1['id'] => $goods1,
            $goods2['id'] => $goods2,
        ], $result);
    }

    public function testFindGoodsSpecsByIds()
    {
        $goodsSpecs1 = $this->createGoodsSpecs();
        $goodsSpecs2 = $this->createGoodsSpecs(['targetId' => 1]);
        $goodsSpecs3 = $this->createGoodsSpecs(['goodsId' => 1]);
        $goodsSpecs4 = $this->createGoodsSpecs(['price' => '10.00']);

        $expected = ArrayToolkit::index([$goodsSpecs1, $goodsSpecs4], 'id');

        $result = $this->getGoodsService()->findGoodsSpecsByIds([$goodsSpecs1['id'], $goodsSpecs4['id']]);
        $this->assertEquals($expected, $result);
    }

    protected function createGoods($goods = [])
    {
        $default = [
            'productId' => 1,
            'type' => 'course',
            'title' => 'testTitle',
            'images' => [],
        ];

        $goods = array_merge($default, $goods);

        return $this->getGoodsDao()->create($goods);
    }

    protected function createGoodsSpecs($goodsSpecs = [])
    {
        $default = [
            'goodsId' => 1,
            'targetId' => 1,
            'title' => 'testTitle',
            'images' => [],
            'price' => '1.00',
        ];

        $goodsSpecs = array_merge($default, $goodsSpecs);

        return $this->getGoodsSpecsDao()->create($goodsSpecs);
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

    /**
     * @return GoodsSpecsDao
     */
    protected function getGoodsSpecsDao()
    {
        return $this->createDao('Goods:GoodsSpecsDao');
    }
}
