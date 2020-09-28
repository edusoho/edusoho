<?php

namespace Tests\Unit\Goods\Entity;

use Biz\BaseTestCase;
use Biz\Classroom\ClassroomException;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\Classroom\Service\ClassroomService;
use Biz\Goods\GoodsEntityFactory;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;

class ClassroomEntityTest extends BaseTestCase
{
    public function testGetTarget()
    {
        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        list($product, $goods) = $this->getProductAndGoods($classroom);
        self::assertEquals($classroom, $this->getGoodsEntityFactory()->create('classroom')->getTarget($goods));
    }

    public function testGetTarget_whenProductUnExist()
    {
        $this->expectException(ClassroomException::class);
        $this->expectExceptionCode(ClassroomException::NOTFOUND_CLASSROOM);

        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $this->getClassroomDao()->delete($classroom['id']);
        $this->getGoodsEntityFactory()->create('classroom')->getTarget($goods);
    }

    public function testHitTarget()
    {
        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $hitNum = $this->getGoodsEntityFactory()->create('classroom')->hitTarget($goods);
        self::assertEquals(1, $hitNum);
    }

    public function testGetSpecsByTargetId()
    {
        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        $specs = $this->getGoodsEntityFactory()->create('classroom')->getSpecsByTargetId($classroom['id']);
        self::assertEquals($classroom['title'], $specs['title']);
    }

    public function testFetchTargets()
    {
        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $goodsLists = $this->getGoodsEntityFactory()->create('classroom')->fetchTargets([$goods]);
        self::assertEquals($classroom, reset($goodsLists)['classroom']);
    }

    public function testFetchTargets_whenEmpty()
    {
        $goodsLists = $this->getGoodsEntityFactory()->create('classroom')->fetchTargets([]);
        self::assertEmpty($goodsLists);
    }

    public function testFetchSpecs()
    {
        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        $classrooms = $this->getGoodsEntityFactory()->create('classroom')->fetchSpecs([$classroom]);
        self::assertEquals($classroom['title'], reset($classrooms)['spec']['title']);
    }

    public function testFetchSpecs_whenEmpty()
    {
        $classrooms = $this->getGoodsEntityFactory()->create('classroom')->fetchSpecs([]);
        self::assertEmpty($classrooms);
    }

    public function testCanManageTarget()
    {
        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        list($product, $goods) = $this->getProductAndGoods($classroom);
        self::assertTrue($this->getGoodsEntityFactory()->create('classroom')->canManageTarget($goods));
    }

    public function testBuySpecsAccess()
    {
        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        $this->getClassroomService()->publishClassroom($classroom['id']);
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $specs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);
        $access = $this->getGoodsEntityFactory()->create('classroom')->buySpecsAccess($goods, $specs);

        // 班级的创建者只是班级的班主任，但是不是班级的教师，所以当前业务上返回可以加入
        self::assertEquals('success', $access['code']);
    }

    public function testIsSpecsStudent()
    {
        $classroom = $this->getClassroomService()->addClassroom($this->mockClassroom());
        $this->getClassroomService()->publishClassroom($classroom['id']);
        list($product, $goods) = $this->getProductAndGoods($classroom);
        $specs = $this->getGoodsService()->getGoodsSpecsByGoodsIdAndTargetId($goods['id'], $classroom['id']);
        $isMember = $this->getGoodsEntityFactory()->create('classroom')->isSpecsMember($goods, $specs, $this->getCurrentUser()->getId());
        self::assertFalse($isMember);
    }

    private function getProductAndGoods($classroom)
    {
        $product = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
        $goods = $this->getGoodsService()->getGoodsByProductId($product['id']);

        return [$product, $goods];
    }

    private function mockClassroom($customFields = [])
    {
        return array_merge([
            'id' => 1,
            'title' => '测试班级商品',
            'subtitle' => '测试班级商品副标题',
            'about' => '测试班级商品简介',
            'largePicture' => 'public://course/2018/07-25/150507367180941893.jpg',
            'smallPicture' => 'public://course/2018/07-25/150507367180941893.jpg',
            'middlePicture' => 'public://course/2018/07-25/150507367180941893.jpg',
            'orgCode' => '1.1',
            'orgId' => 1,
            'creator' => 1,
            'price' => '0.00',
            'showable' => 1,
            'buyable' => 1,
            'expiryMode' => 'forever',
            'service' => [],
        ], $customFields);
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return GoodsEntityFactory
     */
    protected function getGoodsEntityFactory()
    {
        $biz = $this->biz;

        return $biz['goods.entity.factory'];
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('Product:ProductService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }

    /**
     * @return ClassroomDao
     */
    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:ClassroomDao');
    }
}
