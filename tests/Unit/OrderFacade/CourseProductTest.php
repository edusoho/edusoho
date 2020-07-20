<?php

namespace Tests\Unit\OrderFacade;

use Biz\Accessor\AccessorInterface;
use Biz\BaseTestCase;
use Biz\Course\CourseException;
use Biz\OrderFacade\Product\CourseProduct;
use Codeages\Biz\Order\Status\OrderStatusCallback;

class CourseProductTest extends BaseTestCase
{
    public function testValidate()
    {
        $courseProduct = new CourseProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => ['buyable' => true]],
            ['functionName' => 'canJoinCourse', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);

        $this->assertEquals(null, $courseProduct->validate());
    }

    /**
     * @expectedException  \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateOnErrorWhenCourseUnPurchasable()
    {
        $courseProduct = new CourseProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => ['buyable' => 0]],
            ['functionName' => 'canJoinCourse', 'returnValue' => ['code' => AccessorInterface::SUCCESS]],
        ]);

        $courseProduct->validate();
    }

    /**
     * @expectedException \Biz\OrderFacade\Exception\OrderPayCheckException
     */
    public function testValidateWithError()
    {
        $courseProduct = new CourseProduct();
        $courseProduct->setBiz($this->getBiz());

        $this->mockBiz('Course:CourseService', [
            ['functionName' => 'getCourse', 'returnValue' => ['buyable' => true]],
            ['functionName' => 'canJoinCourse', 'returnValue' => ['class' => 'Biz\Course\CourseException', 'code' => 'UNBUYABLE_COURSE', 'msg' => 'wrong']],
        ]);

        $courseProduct->validate();
    }

    public function testInit()
    {
        list($goodsSpecs, $course, $courseSet) = $this->mockData();

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $product->init(['targetId' => $goodsSpecs['id']]);

        $this->assertEquals($product->targetId, $goodsSpecs['id']);
        $this->assertEquals($product->courseId, $course['id']);
        $this->assertEquals($product->backUrl, ['routing' => 'course_show', 'params' => ['id' => $course['id']]]);
        $this->assertEquals($product->successUrl, ['my_course_show', ['id' => $course['id']]]);
        $this->assertEquals($product->courseSet, $courseSet);
        $this->assertTrue($product->productEnable);
        $this->assertEquals($product->title, $course['courseSetTitle']);
        $this->assertEquals($product->price, $course['price']);
        $this->assertEquals($product->maxRate, $course['maxRate']);
        $this->assertEquals($product->cover, $courseSet['cover']);
        $this->assertEquals($product->originPrice, $course['originPrice']);
    }

    public function testOnPaid_returnSuccess()
    {
        list($goodsSpecs, $course, $courseSet) = $this->mockData();

        $order = [
            'id' => 1,
            'created_reason' => 'test reason',
        ];
        $orderItem = [
            'user_id' => $this->getCurrentUser()->getId(),
            'order_id' => $order['id'],
            'title' => $course['courseSetTitle'],
            'order' => ['pay_amount' => '1.00'],
            'target_id' => $goodsSpecs['id'],
        ];

        $this->mockBiz('Order:OrderService', [
            [
                'functionName' => 'getOrder',
                'withParams' => [$orderItem['order_id']],
                'returnValue' => $order,
            ],
        ]);

        $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'isCourseStudent',
                'returnValue' => true,
            ],
        ]);

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());

        $result = $product->onPaid($orderItem);
        $this->assertEquals(OrderStatusCallback::SUCCESS, $result);
    }

    public function testOnPaid_whenThrowException_thenReturnFalse()
    {
        list($goodsSpecs, $course, $courseSet) = $this->mockData();

        $order = [
            'id' => 1,
            'created_reason' => 'test reason',
        ];
        $orderItem = [
            'user_id' => $this->getCurrentUser()->getId(),
            'order_id' => $order['id'],
            'title' => $course['courseSetTitle'],
            'order' => ['pay_amount' => '1.00'],
            'target_id' => $goodsSpecs['id'],
        ];

        $this->mockBiz('Order:OrderService', [
            [
                'functionName' => 'getOrder',
                'withParams' => [$orderItem['order_id']],
                'returnValue' => $order,
            ],
        ]);

        $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'isCourseStudent',
                'throwException' => new CourseException(CourseException::NOTFOUND_COURSE),
            ],
        ]);

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $this->assertFalse($product->onPaid($orderItem));
    }

    public function testOnOrderRefundAuditing()
    {
        list($goodsSpecs, $course, $courseSet) = $this->mockData();

        $orderRefundItem = ['order_item' => [
            'user_id' => $this->getCurrentUser()->getId(),
            'target_id' => $goodsSpecs['id'],
        ]];

        $memberService = $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'lockStudent',
            ],
        ]);

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundAuditing($orderRefundItem);
        $memberService->shouldHaveReceived('lockStudent')->times(1);
    }

    public function testOnOrderRefundCancel()
    {
        list($goodsSpecs, $course, $courseSet) = $this->mockData();

        $orderRefundItem = ['order_item' => [
            'user_id' => $this->getCurrentUser()->getId(),
            'target_id' => $goodsSpecs['id'],
        ]];

        $memberService = $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'unlockStudent',
            ],
        ]);

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundCancel($orderRefundItem);
        $memberService->shouldHaveReceived('unlockStudent')->times(1);
    }

    public function testOnOrderRefundRefunded_whenHasMember_thenRemoveStudent()
    {
        list($goodsSpecs, $course, $courseSet) = $this->mockData();

        $orderRefundItem = ['order_item' => [
            'user_id' => $this->getCurrentUser()->getId(),
            'target_id' => $goodsSpecs['id'],
            'refund_id' => 1,
            'order_id' => 1,
        ]];

        $memberService = $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'getCourseMember',
                'returnValue' => ['id' => 1],
            ],
            [
                'functionName' => 'removeStudent',
            ],
        ]);

        $this->mockBiz('OrderFacade:OrderRefundService', [
            [
                'functionName' => 'getOrderRefundById',
                'returnValue' => [
                    'reason' => 'test reason',
                    'id' => 1,
                    'order_id' => 1,
                ],
            ],
        ]);

        $this->mockBiz('MemberOperation:MemberOperationService', [
            [
                'functionName' => 'updateRefundInfoByOrderId',
            ],
        ]);

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundRefunded($orderRefundItem);
        $memberService->shouldHaveReceived('removeStudent')->times(1);
    }

    public function testOnOrderRefundRefunded_whenNotHasMember_thenDoNotRemoveMember()
    {
        list($goodsSpecs, $course, $courseSet) = $this->mockData();

        $orderRefundItem = ['order_item' => [
            'user_id' => $this->getCurrentUser()->getId(),
            'target_id' => $goodsSpecs['id'],
            'refund_id' => 1,
            'order_id' => 1,
        ]];

        $memberService = $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'getCourseMember',
                'returnValue' => null,
            ],
            [
                'functionName' => 'removeStudent',
            ],
        ]);

        $this->mockBiz('OrderFacade:OrderRefundService', [
            [
                'functionName' => 'getOrderRefundById',
                'returnValue' => [
                    'reason' => 'test reason',
                    'id' => 1,
                    'order_id' => 1,
                ],
            ],
        ]);

        $this->mockBiz('MemberOperation:MemberOperationService', [
            [
                'functionName' => 'updateRefundInfoByOrderId',
            ],
        ]);

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundRefunded($orderRefundItem);
        $memberService->shouldNotHaveReceived('removeStudent');
    }

    public function testOnOrderRefundRefused()
    {
        list($goodsSpecs, $course, $courseSet) = $this->mockData();

        $orderRefundItem = ['order_item' => [
            'user_id' => $this->getCurrentUser()->getId(),
            'target_id' => $goodsSpecs['id'],
            'refund_id' => 1,
            'order_id' => 1,
        ]];

        $memberService = $this->mockBiz('Course:MemberService', [
            [
                'functionName' => 'unlockStudent',
            ],
        ]);

        $product = new CourseProduct();
        $product->setBiz($this->getBiz());
        $product->onOrderRefundRefused($orderRefundItem);

        $memberService->shouldHaveReceived('unlockStudent')->times(1);
    }

    protected function mockData($goodsSpecs = [], $course = [], $courseSet = [])
    {
        $goodsSpecs = array_merge([
            'id' => 2,
        ], $goodsSpecs);

        $course = array_merge([
            'id' => 3,
            'status' => 'published',
            'courseSetTitle' => 'test course-set title',
            'price' => '1.00',
            'originPrice' => '1.00',
            'maxRate' => '100',
        ], $course);

        $courseSet = array_merge([
            'id' => 1,
            'status' => 'published',
            'cover' => 'test course-set cover',
        ], $courseSet);

        $goodsSpecs['targetId'] = $course['id'];
        $course['courseSetId'] = $courseSet['id'];

        $this->mockBiz('Goods:GoodsService', [
            [
                'functionName' => 'getGoodsSpecs',
                'withParams' => [$goodsSpecs['id']],
                'returnValue' => $goodsSpecs,
            ],
        ]);

        $this->mockBiz('Course:CourseService', [
            [
                'functionName' => 'getCourse',
                'withParams' => [$goodsSpecs['targetId']],
                'returnValue' => $course,
            ],
        ]);

        $this->mockBiz('Course:CourseSetService', [
            [
                'functionName' => 'getCourseSet',
                'withParams' => [$course['courseSetId']],
                'returnValue' => $courseSet,
            ],
        ]);

        return [$goodsSpecs, $course, $courseSet];
    }
}
