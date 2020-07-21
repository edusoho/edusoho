<?php

namespace Tests\Unit\OrderFacade\Service;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\OrderFacade\Product\ClassroomProduct;
use Biz\OrderFacade\Product\CourseProduct;
use Biz\OrderFacade\Service\OrderFacadeService;
use Biz\System\Service\LogService;

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

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => [
                    'id' => 1,
                    'title' => 'course name1',
                    'courseSetTitle' => 'course set',
                    'price' => 100,
                    'originPrice' => 200,
                    'courseSetId' => 1,
                    'status' => 'published',
                    'maxRate' => 0,
                    'buyable' => true,
                ],
            ],
            [
                'functionName' => 'canJoinCourse',
                'returnValue' => ['code' => AccessorInterface::SUCCESS],
            ],
        ]);
        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];
        $courseProduct->init(['targetId' => 1]);

        $courseProduct->pickedDeducts = [
            ['deduct_id' => 1, 'deduct_type' => 'rewardPoint', 'deduct_amount' => 20],
            ['deduct_id' => 2, 'deduct_type' => 'discount', 'deduct_amount' => 100],
            ['deduct_id' => 2, 'deduct_type' => 'seckill', 'deduct_amount' => 20, 'deduct_type_name' => '秒杀'],
        ];

        $order = $this->getOrderFacadeService()->create($courseProduct);

        $this->assertEquals(60 * 100, $order['pay_amount']);
        $this->assertEquals('course set-course name1', $order['title']);
    }

    /**
     * @expectedException \Biz\Order\OrderException
     */
    public function testCheckOrderBeforePay()
    {
        $this->mockBiz('Order:OrderService', [
           ['functionName' => 'getOrderBySn', 'returnValue' => []],
        ]);

        $this->getOrderFacadeService()->checkOrderBeforePay('12456', []);
    }

    public function testCreateCourseImportOrder()
    {
        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'returnValue' => [
                    'id' => 1,
                    'title' => 'course name1',
                    'courseSetTitle' => 'course set',
                    'price' => 1,
                    'originPrice' => 10,
                    'courseSetId' => 1,
                    'status' => 'published',
                    'maxRate' => 0,
                ],
            ],
        ]);

        $this->mockBiz('Course:MemberService', [
            ['functionName' => 'becomeStudent', 'returnValue' => ['id' => 1, 'courseId' => 1, 'userId' => 10]],
            ['functionName' => 'isCourseStudent', 'returnValue' => false],
        ]);
        $this->mockBiz('Course:CourseSetService', [
            ['functionName' => 'getCourseSet', 'returnValue' => ['id' => 1, 'title' => 'course set name1', 'cover' => '', 'status' => 'published']],
        ]);

        $biz = $this->getBiz();
        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];

        $courseProduct->init(['targetId' => 1]);
        $courseProduct->price = 10;

        $params = [
            'created_reason' => '课程用户导入订单',
            'price_type' => 'CNY',
        ];
        $order = $this->getOrderFacadeService()->createSpecialOrder($courseProduct, 10, $params);

        $this->assertEquals('paid', $order['status']);
        $this->assertEquals('course set-course name1', $order['title']);
        $this->assertArraySubset($params, $order);
    }

    public function testCreateClassroomImportOrder()
    {
        $this->mockBiz('Classroom:ClassroomService', [
            ['functionName' => 'getClassroom', 'returnValue' => ['id' => 1, 'title' => 'classroom name1', 'price' => 10, 'middlePicture' => '', 'status' => 'published', 'maxRate' => 0, 'smallPicture' => '', 'largePicture' => '']],
            ['functionName' => 'isClassroomStudent', 'returnValue' => false],
            ['functionName' => 'becomeStudent', 'returnValue' => []],
        ]);

        $biz = $this->getBiz();
        $product = $biz['order.product.'.ClassroomProduct::TYPE];

        $product->init(['targetId' => 1]);
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
}
