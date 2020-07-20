<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\Classroom\ClassroomException;
use Biz\OrderFacade\Product\ClassroomProduct;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class ClassroomProductTest extends BaseTestCase
{
    public function testValidate()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Classroom:ClassroomService', [
            ['functionName' => 'getClassroom', 'returnValue' => ['buyable' => true]],
            ['functionName' => 'canJoinClassroom', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);
        $this->assertEquals(null, $classroomProduct->validate());
    }

    /**
     * @expectedException  \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateOnErrorWhenClassroomUnPurchasable()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Classroom:ClassroomService', [
            ['functionName' => 'getClassroom', 'returnValue' => ['buyable' => 0]],
            ['functionName' => 'canJoinClassroom', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);

        $classroomProduct->validate();
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateWithError()
    {
        $classroomProduct = new ClassroomProduct();
        $classroomProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'canJoinCourse', 'returnValue' => ['code' => 'error', 'msg' => 'wrong']],
        ]);

        $classroomProduct->validate();
    }

    public function testInit()
    {
        list($goodsSpecs, $classroom) = $this->mockData();

        $product = new ClassroomProduct();
        $product->setBiz($this->getBiz());
        $product->init(['targetId' => $goodsSpecs['id']]);

        $this->assertEquals($product->targetId, $goodsSpecs['id']);
        $this->assertEquals($product->classroomId, $classroom['id']);
        $this->assertEquals($product->backUrl, ['routing' => 'classroom_show', 'params' => ['id' => $classroom['id']]]);
        $this->assertEquals($product->successUrl, ['classroom_show', ['id' => $classroom['id']]]);
        $this->assertEquals($product->title, $classroom['title']);
        $this->assertEquals($product->originPrice, $classroom['price']);
        $this->assertEquals($product->middlePicture, $classroom['middlePicture']);
        $this->assertEquals($product->maxRate, $classroom['maxRate']);
        $this->assertTrue($product->productEnable);
        $this->assertEquals($product->cover, [
            'small' => $classroom['smallPicture'],
            'middle' => $classroom['middlePicture'],
            'large' => $classroom['largePicture'],
        ]);
    }

    public function testOnPaid_whenIsNotStudent_thenBecomeStudent()
    {
        list($goodsSpecs, $classroom) = $this->mockData();

        $order = [
            'id' => 1,
            'created_reason' => 'test created reason',
        ];

        $orderItem = [
            'order_id' => $order['id'],
            'target_id' => $goodsSpecs['id'],
            'user_id' => $this->getCurrentUser()->getId(),
        ];

        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'returnValue' => [],
            ],
        ]);

        $this->mockBiz('Order:OrderService', [
            [
                'functionName' => 'getOrder',
                'withParams' => [$orderItem['order_id']],
                'returnValue' => $order,
            ],
        ]);

        $classroomService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$classroom['id']],
                'returnValue' => $classroom,
            ],
            [
                'functionName' => 'isClassroomStudent',
                'returnValue' => false,
            ],
            [
                'functionName' => 'becomeStudent',
                'returnValue' => ['id' => 1],
            ],
        ]);

        $product = new ClassroomProduct();
        $product->setBiz($this->getBiz());
        $result = $product->onPaid($orderItem);

        $this->assertEquals(OrderStatusCallback::SUCCESS, $result);

        $classroomService->shouldHaveReceived('becomeStudent')->times(1);
    }

    public function testOnPaid_whenThrowException_thenReturnFalse()
    {
        list($goodsSpecs, $classroom) = $this->mockData();

        $order = [
            'id' => 1,
            'created_reason' => 'test created reason',
        ];

        $orderItem = [
            'order_id' => $order['id'],
            'target_id' => $goodsSpecs['id'],
            'user_id' => $this->getCurrentUser()->getId(),
        ];

        $this->mockBiz('System:SettingService', [
            [
                'functionName' => 'get',
                'returnValue' => [],
            ],
        ]);

        $this->mockBiz('Order:OrderService', [
            [
                'functionName' => 'getOrder',
                'withParams' => [$orderItem['order_id']],
                'returnValue' => $order,
            ],
        ]);

        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$classroom['id']],
                'returnValue' => $classroom,
            ],
            [
                'functionName' => 'isClassroomStudent',
                'returnValue' => false,
            ],
            [
                'functionName' => 'becomeStudent',
                'throwException' => new ClassroomException(ClassroomException::NOTFOUND_CLASSROOM),
            ],
        ]);

        $product = new ClassroomProduct();
        $product->setBiz($this->getBiz());

        $this->assertFalse($product->onPaid($orderItem));
    }

    public function testOnOrderRefundAuditing()
    {
        list($goodsSpecs, $classroom) = $this->mockData();

        $orderRefundItem = [
            'order_item' => [
                'order_id' => 1,
                'target_id' => $goodsSpecs['id'],
                'user_id' => $this->getCurrentUser()->getId(),
            ],
        ];

        $classroomService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$classroom['id']],
                'returnValue' => $classroom,
            ],
            [
                'functionName' => 'lockStudent',
            ],
        ]);

        $product = new ClassroomProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundAuditing($orderRefundItem);

        $classroomService->shouldHaveReceived('lockStudent')->times(1);
    }

    public function testOnOrderRefundCancel()
    {
        list($goodsSpecs, $classroom) = $this->mockData();

        $orderRefundItem = [
            'order_item' => [
                'order_id' => 1,
                'target_id' => $goodsSpecs['id'],
                'user_id' => $this->getCurrentUser()->getId(),
            ],
        ];

        $classroomService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$classroom['id']],
                'returnValue' => $classroom,
            ],
            [
                'functionName' => 'unlockStudent',
            ],
        ]);

        $product = new ClassroomProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundCancel($orderRefundItem);

        $classroomService->shouldHaveReceived('unlockStudent')->times(1);
    }

    public function testOnOrderRefundRefunded()
    {
        list($goodsSpecs, $classroom) = $this->mockData();

        $orderRefundItem = [
            'order_item' => [
                'order_id' => 1,
                'target_id' => $goodsSpecs['id'],
                'user_id' => $this->getCurrentUser()->getId(),
                'refund_id' => 1,
            ],
        ];

        $classroomService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$classroom['id']],
                'returnValue' => $classroom,
            ],
            [
                'functionName' => 'unlockStudent',
            ],
            [
                'functionName' => 'getClassroomMember',
                'returnValue' => ['id' => 2],
            ],
            [
                'functionName' => 'removeStudent',
            ],
        ]);

        $this->mockBiz('OrderFacade:OrderRefundService', [
            [
                'functionName' => 'getOrderRefundById',
            ],
        ]);

        $this->mockBiz('MemberOperation:MemberOperationService', [
            [
                'functionName' => 'updateRefundInfoByOrderId',
            ],
        ]);

        $product = new ClassroomProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundRefunded($orderRefundItem);

        $classroomService->shouldHaveReceived('removeStudent')->times(1);
    }

    public function testOnOrderRefundRefused()
    {
        list($goodsSpecs, $classroom) = $this->mockData();

        $orderRefundItem = [
            'order_item' => [
                'order_id' => 1,
                'target_id' => $goodsSpecs['id'],
                'user_id' => $this->getCurrentUser()->getId(),
                'refund_id' => 1,
            ],
        ];

        $classroomService = $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$classroom['id']],
                'returnValue' => $classroom,
            ],
            [
                'functionName' => 'unlockStudent',
            ],
        ]);

        $product = new ClassroomProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundRefused($orderRefundItem);

        $classroomService->shouldHaveReceived('unlockStudent')->times(1);
    }

    protected function mockData($goodsSpecs = [], $classroom = [])
    {
        $goodsSpecs = array_merge([
            'id' => 2,
        ], $goodsSpecs);

        $classroom = array_merge([
            'id' => 1,
            'title' => 'test title',
            'price' => '1.00',
            'smallPicture' => 'test small picture',
            'middlePicture' => 'test middle picture',
            'largePicture' => 'test large picture',
            'maxRate' => 100,
            'status' => 'published',
        ], $classroom);

        $goodsSpecs['targetId'] = $classroom['id'];

        $this->mockBiz('Goods:GoodsService', [
            [
                'functionName' => 'getGoodsSpecs',
                'withParams' => [$goodsSpecs['id']],
                'returnValue' => $goodsSpecs,
            ],
        ]);

        $this->mockBiz('Classroom:ClassroomService', [
            [
                'functionName' => 'getClassroom',
                'withParams' => [$classroom['id']],
                'returnValue' => $classroom,
            ],
        ]);

        return [$goodsSpecs, $classroom];
    }
}
