<?php

namespace Tests\Unit\OrderFacade\Service;

use Biz\BaseTestCase;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Service\GoodsService;
use Biz\Order\OrderException;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\LogService;
use Biz\User\CurrentUser;
use Biz\User\Dao\UserDao;
use Ramsey\Uuid\Uuid;

class OrderFacadeServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->mockCurrency();
    }

    public function testCreate()
    {
        $biz = $this->getBiz();

        $course = $this->createCourse([
            'title' => 'course name1',
            'price' => 100,
            'originPrice' => 200,
            'courseSetId' => 1,
            'status' => 'published',
            'maxRate' => 0,
            'buyable' => true,
        ]);

        $product = $this->getProductService()->getProductByTargetIdAndType($course['id'], 'course');
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($product['id'], $course['id']);

        $this->setNewCurrentUser();

        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];
        $courseProduct->init(['targetId' => $goodsSpecs['id']]);

        $courseProduct->pickedDeducts = [
            ['deduct_id' => 1, 'deduct_type' => 'rewardPoint', 'deduct_amount' => 20],
            ['deduct_id' => 2, 'deduct_type' => 'discount', 'deduct_amount' => 100],
            ['deduct_id' => 2, 'deduct_type' => 'seckill', 'deduct_amount' => 20, 'deduct_type_name' => '秒杀'],
        ];

        $order = $this->getOrderFacadeService()->create($courseProduct);
        $this->assertEquals(60 * 100, $order['pay_amount']);
        $this->assertEquals('course name1-course name1', $order['title']);
    }

    public function testCheckOrderBeforePay()
    {
        $this->expectException(OrderException::class);

        $this->mockBiz('Order:OrderService', [
            ['functionName' => 'getOrderBySn', 'returnValue' => []],
        ]);

        $this->getOrderFacadeService()->checkOrderBeforePay('12456', []);
    }

    public function testCreateCourseImportOrder()
    {
        $course = $this->createCourse([
            'title' => 'course name1',
            'price' => 100,
            'originPrice' => 200,
            'courseSetId' => 1,
            'status' => 'published',
            'maxRate' => 0,
            'buyable' => true,
        ]);

        $product = $this->getProductService()->getProductByTargetIdAndType($course['id'], 'course');
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($product['id'], $course['id']);

        $this->mockBiz('Course:MemberService', [
            ['functionName' => 'becomeStudent', 'returnValue' => ['id' => 1, 'courseId' => 1, 'userId' => 10]],
            ['functionName' => 'isCourseStudent', 'returnValue' => false],
        ]);

        $biz = $this->getBiz();
        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];

        $courseProduct->init(['targetId' => $goodsSpecs['id']]);
        $courseProduct->price = 10;

        $params = [
            'created_reason' => '课程用户导入订单',
            'price_type' => 'CNY',
        ];
        $order = $this->getOrderFacadeService()->createSpecialOrder($courseProduct, 10, $params);

        $this->assertEquals('paid', $order['status']);
        $this->assertEquals('course name1-course name1', $order['title']);
        $this->assertArraySubset($params, $order);
    }

    public function testCreateClassroomImportOrder()
    {
        $classroom = $this->createClassroom();

        $product = $this->getProductService()->getProductByTargetIdAndType($classroom['id'], 'classroom');
        $goodsSpecs = $this->getGoodsService()->getGoodsSpecsByProductIdAndTargetId($product['id'], $classroom['id']);

        $biz = $this->getBiz();
        $product = $biz['order.product.'.ClassroomProduct::TYPE];

        $product->init(['targetId' => $goodsSpecs['id']]);
        $product->price = 10;

        $params = [
            'created_reason' => '班级用户导入订单',
            'price_type' => 'CNY',
        ];
        $order = $this->getOrderFacadeService()->createSpecialOrder($product, 10, $params);

        $this->assertEquals('paid', $order['status']);
        $this->assertEquals(10 * 100, $order['price_amount']);
        $this->assertArraySubset($params, $order);
    }

    public function testAdjustOrderPrice()
    {
        $mockAdjustDeduct = [
            'deduct_amount' => 200,
            'order' => [
                'title' => 'order',
            ],
        ];
        $this->mockBiz('Order:WorkflowService', [
            ['functionName' => 'adjustPrice', 'returnValue' => $mockAdjustDeduct],
        ]);

        $result = $this->getOrderFacadeService()->adjustOrderPrice(1, 2000);

        $this->assertSame($mockAdjustDeduct, $result);
        $log = $this->getLogService()->searchLogs(['module' => 'order', 'action' => OrderFacadeService::DEDUCT_TYPE_ADJUST], [], 0, 1);
        $this->assertNotNull($log);
    }

    public function testGetOrderAdjustInfo()
    {
        $this->mockBiz('Order:OrderService', [
            ['functionName' => 'findOrderItemDeductsByOrderId', 'returnValue' => [
                ['deduct_type' => 'discount', 'deduct_amount' => 2000],
                ['deduct_type' => OrderFacadeService::DEDUCT_TYPE_ADJUST, 'deduct_amount' => 1000],
            ]],
        ]);

        $order = ['id' => 1, 'price_amount' => 10000, 'pay_amount' => 7000];
        $adjustInfo = $this->getOrderFacadeService()->getOrderAdjustInfo($order);

        $this->assertArrayEquals(
            $adjustInfo,
            ['payAmountExcludeAdjust' => 80, 'adjustPrice' => 10, 'adjustDiscount' => 8.75],
            ['payAmountExcludeAdjust', 'adjustPrice', 'adjustDiscount']
        );
    }

    private function mockCurrency()
    {
        $biz = $this->getBiz();

        $currency = $this->getMockBuilder('Biz\OrderFacade\Currency')
            ->disableOriginalConstructor()
            ->getMock();
        $currency->isoCode = 'CNY';
        $biz['currency'] = $currency;
    }

    protected function createCourse($courseFields = [])
    {
        $courseFields = array_merge([
            'type' => 'normal',
            'title' => 'test course title',
            'about' => 'course about',
            'summary' => 'course summary',
            'price' => '100.00',
            'originPrice' => '100.00',
            'isFree' => 1,
            'buyable' => 1,
        ], $courseFields);

        $courseSet = $this->getCourseSetService()->createCourseSet($courseFields);

        $course = $this->getCourseService()->getCourse($courseSet['defaultCourseId']);

        $this->getCourseService()->updateCourse($course['id'], $courseFields);
        $this->getCourseService()->updateBaseInfo($course['id'], $courseFields);

        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        return $this->getCourseService()->getCourse($course['id']);
    }

    protected function createClassroom($classroomFields = [])
    {
        $classroomFields = array_merge([
            'title' => 'classroom name1',
            'price' => 10, 'middlePicture' => '',
            'status' => 'published', 'maxRate' => 0,
            'smallPicture' => '',
            'largePicture' => '',
        ], $classroomFields);

        $classroom = $this->getClassroomService()->addClassroom($classroomFields);
        $this->getClassroomService()->updateClassroom($classroom['id'], $classroomFields);
        $this->getClassroomService()->publishClassroom($classroom['id']);

        return $this->getClassroomService()->getClassroom($classroom['id']);
    }

    protected function setNewCurrentUser($newUser = [])
    {
        $newUser = array_merge([
            'nickname' => 'test_user',
            'type' => 'default',
            'email' => 'defaultUser@howzhi.com',
            'password' => '123123',
            'salt' => 'salt1',
            'roles' => ['ROLE_USER'],
            'uuid' => Uuid::uuid4(),
        ], $newUser);

        $user = $this->getUserDao()->create($newUser);

        $user['currentIp'] = '127.0.0.1';

        $currentUser = new CurrentUser();
        $this->getServiceKernel()->setCurrentUser($currentUser->fromArray($user));
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->createService('OrderFacade:OrderFacadeService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UserDao
     */
    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
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
}
