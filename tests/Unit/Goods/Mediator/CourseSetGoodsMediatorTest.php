<?php

namespace Tests\Unit\Goods\Mediator;

use Biz\BaseTestCase;
use Biz\Goods\Mediator\CourseSetGoodsMediator;
use Biz\Goods\Service\GoodsService;

class CourseSetGoodsMediatorTest extends BaseTestCase
{
    public function testOnCreate()
    {
//        $courseSetMediator = $this->getCourseSetGoodsMediator();
//        $courseSetMediator->onCreate($this->mockCourseSetData());
    }

    public function testOnUpdateNormalFields()
    {
    }

    protected function mockCourseSetData($customFields = [])
    {
        return array_merge([
            'id' => 1,
            'title' => '测试构建商品的课程',
            'subtitle' => '副标题',
        ], $customFields);
    }

    /**
     * @return CourseSetGoodsMediator
     */
    protected function getCourseSetGoodsMediator()
    {
        return $this->biz['goods.mediator.course_set'];
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
