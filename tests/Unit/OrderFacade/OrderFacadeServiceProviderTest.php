<?php

namespace Tests\Unit\OrderFacade;

use Biz\BaseTestCase;
use Biz\OrderFacade\OrderFacadeServiceProvider;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\OrderFacade\Product\CourseProduct;

class OrderFacadeServiceProviderTest extends BaseTestCase
{
    public function testRegister()
    {
        $biz = $this->getBiz();

        $biz->register(new OrderFacadeServiceProvider());

        $product1 = $biz['order.product.'.CourseProduct::TYPE];
        $product2 = $biz['order.product.'.CourseProduct::TYPE];

        $this->assertNotSame($product1, $product2);
        $this->assertInstanceOf('Biz\OrderFacade\Product\CourseProduct', $biz['order.product.'.CourseProduct::TYPE]);
        $this->assertInstanceOf('Biz\OrderFacade\Product\ClassroomProduct', $biz['order.product.'.ClassroomProduct::TYPE]);

        $this->assertInstanceOf('Biz\OrderFacade\Command\Deduct\PickedDeductWrapper', $biz['order.product.picked_deduct_wrapper']);
        $this->assertInstanceOf('Biz\OrderFacade\Command\Deduct\AvailableDeductWrapper', $biz['order.product.available_deduct_wrapper']);
    }

    public function testCreateCourseImportOrder()
    {
        $biz = $this->getBiz();

        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array('id' => 1,'title' => 'course name1', 'price' => 10 ,'courseSetId' => 1, 'status' => 'published')),
        ));

        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'becomeStudent', 'returnValue' => array('id' => 1,'courseId' => 1, 'userId' => 10 )),
            array('functionName' => 'isCourseStudent', 'returnValue' => false),
        ));
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'getCourseSet', 'returnValue' => array('id' => 1,'title' => 'course set name1'))
        ));

        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];

        $courseProduct->init(array('targetId' => 1));
        $courseProduct->price = 10;

        $params = array(
            'created_reason' => '课程用户导入订单',
            'source' => 'self-outside',
            'price_type' => 'CNY'
        );
        $order = $this->getOrderFacadeService()->createImportOrder($courseProduct, 10, $params);

        $this->assertEquals('paid', $order['status']);
        $this->assertArraySubset($params, $order);
    }

    public function testCreateClassroomImportOrder()
    {
        $biz = $this->getBiz();

        $this->mockBiz('Classroom:ClassroomService', array(
            array('functionName' => 'getClassroom', 'returnValue' => array('id' => 1,'title' => 'classroom name1', 'price' => 10 ,'middlePicture' => '', 'status' => 'published')),
            array('functionName' => 'isClassroomStudent', 'returnValue' => false),
            array('functionName' => 'becomeStudent', 'returnValue' => array())
        ));

        $product = $biz['order.product.'.ClassroomProduct::TYPE];

        $product->init(array('targetId' => 1));
        $product->price = 10;

        $params = array(
            'created_reason' => '班级用户导入订单',
            'source' => 'self-outside',
            'price_type' => 'CNY'
        );
        $order = $this->getOrderFacadeService()->createImportOrder($product, 10, $params);

        $this->assertEquals('paid', $order['status']);
        $this->assertEquals(10 * 100, $order['price_amount']);
        $this->assertArraySubset($params, $order);
    }

    protected function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }
}
