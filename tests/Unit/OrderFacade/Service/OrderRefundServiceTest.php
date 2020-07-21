<?php

namespace Tests\Unit\OrderFacade\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\OrderFacade\Product\CourseProduct;

class OrderRefundServiceTest extends BaseTestCase
{
    public function testSearchRefunds()
    {
        $this->mockBiz(
            'Order:OrderRefundService',
            [
                [
                    'functionName' => 'searchRefunds',
                    'returnValue' => [['id' => 11, 'order_id' => 111]],
                    'withParams' => [['order_id' => 111], [], 0, 5],
                ],
            ]
        );
        $result = $this->getOrderRefundService()->searchRefunds(['order_id' => 111], [], 0, 5);
        $this->assertEquals([['id' => 11, 'order_id' => 111]], $result);
    }

    public function testCountRefunds()
    {
        $this->mockBiz(
            'Order:OrderRefundService',
            [
                [
                    'functionName' => 'countRefunds',
                    'returnValue' => 5,
                    'withParams' => [['order_id' => 111]],
                ],
            ]
        );
        $result = $this->getOrderRefundService()->countRefunds(['order_id' => 111]);
        $this->assertEquals(5, $result);
    }

    public function testGetOrderRefundById()
    {
        $this->mockBiz(
            'Order:OrderRefundService',
            [
                [
                    'functionName' => 'getOrderRefundById',
                    'returnValue' => ['id' => 11, 'order_id' => 111],
                    'withParams' => [11],
                ],
            ]
        );
        $result = $this->getOrderRefundService()->getOrderRefundById(11);
        $this->assertEquals(['id' => 11, 'order_id' => 111], $result);
    }

    public function testApplyOrderRefund()
    {
        $this->mockBiz(
            'Order:OrderService',
            [
                [
                    'functionName' => 'getOrder',
                    'returnValue' => ['id' => 11, 'order_id' => 111, 'created_user_id' => 1, 'pay_amount' => 10, 'refund_deadline' => time() + 1000],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => [['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course']],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'getOrderItem',
                    'returnValue' => ['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course'],
                    'withParams' => [11],
                ],
            ]
        );
        $this->mockBiz(
            'Order:OrderRefundService',
            [
                [
                    'functionName' => 'searchRefunds',
                    'returnValue' => [],
                    'withParams' => [['order_id' => 11, 'status' => 'auditing'], [], 0, PHP_INT_MAX],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'searchRefunds',
                    'returnValue' => [['id' => 11, 'order_id' => 11]],
                    'withParams' => [['order_id' => 11, 'status' => 'auditing'], [], 0, PHP_INT_MAX],
                    'runTimes' => 1,
                ],
            ]
        );
        $this->mockBiz(
            'Order:WorkflowService',
            [
                [
                    'functionName' => 'applyOrderRefund',
                    'returnValue' => ['id' => 11, 'order_id' => 111],
                    'withParams' => [11, ['reason' => 'test']],
                ],
            ]
        );
        $result = $this->getOrderRefundService()->applyOrderRefund(11, ['reason' => 'test']);
        $this->assertEquals(['id' => 11, 'order_id' => 111], $result);

        $result = $this->getOrderRefundService()->applyOrderRefund(11, ['reason' => 'test']);
        $this->assertEquals(['id' => 11, 'order_id' => 11], $result);
    }

    public function testRefuseRefund()
    {
        $currentUser = $this->getCurrentUser();
        $currentUser->setPermissions(['admin' => 1]);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Order:OrderService',
            [
                [
                    'functionName' => 'getOrder',
                    'returnValue' => ['id' => 11, 'order_id' => 111, 'created_user_id' => 1, 'pay_amount' => 10, 'refund_deadline' => time() + 1000, 'user_id' => 1],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => [['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course', 'refund_id' => 11]],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'getOrderItem',
                    'returnValue' => ['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course'],
                    'withParams' => [11],
                ],
            ]
        );
        $this->mockBiz(
            'Order:WorkflowService',
            [
                [
                    'functionName' => 'refuseRefund',
                    'withParams' => [11, []],
                ],
            ]
        );
        $result = $this->getOrderRefundService()->refuseRefund(11, []);
        $this->getWorkflowService()->shouldHaveReceived('refuseRefund');
        $this->assertEquals(111, $result->targetId);
    }

    public function testAdoptRefund()
    {
        $this->mockBiz(
            'Order:OrderService',
            [
                [
                    'functionName' => 'getOrder',
                    'returnValue' => ['id' => 11, 'order_id' => 111, 'created_user_id' => 1, 'pay_amount' => 10, 'refund_deadline' => time() + 1000, 'user_id' => 1],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => [['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course', 'refund_id' => 11]],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'getOrderItem',
                    'returnValue' => ['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course'],
                    'withParams' => [11],
                ],
            ]
        );
        $this->mockBiz(
            'Order:WorkflowService',
            [
                [
                    'functionName' => 'adoptRefund',
                    'withParams' => [11, []],
                ],
            ]
        );
        $result = $this->getOrderRefundService()->adoptRefund(11, []);
        $this->getWorkflowService()->shouldHaveReceived('adoptRefund');
        $this->assertEquals(111, $result->targetId);
    }

    public function testCancelRefund()
    {
        $this->mockBiz(
            'Order:OrderService',
            [
                [
                    'functionName' => 'getOrder',
                    'returnValue' => ['id' => 11, 'order_id' => 111, 'created_user_id' => 1, 'pay_amount' => 10, 'refund_deadline' => time() + 1000, 'user_id' => 1],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => [['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course', 'refund_id' => 11]],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'getOrderItem',
                    'returnValue' => ['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course'],
                    'withParams' => [11],
                ],
            ]
        );
        $this->mockBiz(
            'Order:WorkflowService',
            [
                [
                    'functionName' => 'cancelRefund',
                    'withParams' => [11],
                ],
            ]
        );
        $result = $this->getOrderRefundService()->cancelRefund(11);
        $this->getWorkflowService()->shouldHaveReceived('cancelRefund');
        $this->assertNull($result);
    }

    /**
     * @expectedException \Biz\User\UserException
     */
    public function testTryManageOrderRefund()
    {
        $orderRefundService = $this->getOrderRefundService();
        $currentUser = $this->getCurrentUser();
        $currentUser->setPermissions([]);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        ReflectionUtils::invokeMethod($orderRefundService, 'tryManageOrderRefund');
    }

    public function testGetProductAndOrderItem()
    {
        $orderRefundService = $this->getOrderRefundService();
        $this->mockBiz(
            'Order:OrderService',
            [
                [
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => [['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course', 'refund_id' => 11]],
                    'withParams' => [11],
                ],
                [
                    'functionName' => 'getOrderItem',
                    'returnValue' => ['id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course'],
                    'withParams' => [11],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($orderRefundService, 'getProductAndOrderItem', [['id' => 11]]);
        $this->assertEquals(11, $result[1]['id']);
    }

    public function testNotifyStudent()
    {
        $biz = $this->getBiz();
        $orderRefundService = $this->getOrderRefundService();
        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];
        $courseProduct->init(['targetId' => 1]);
        $message = [
            'type' => 'apply_create',
            'targetId' => $courseProduct->targetId,
            'targetType' => $courseProduct->targetType,
            'title' => $courseProduct->title,
            'userId' => 1,
            'nickname' => 'admin',
        ];
        $this->mockBiz(
            'System:SettingService',
            [
                [
                    'functionName' => 'get',
                    'returnValue' => [],
                    'withParams' => ['refund', []],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'get',
                    'returnValue' => ['applyNotification' => '申请审核'],
                    'withParams' => ['refund', []],
                    'runTimes' => 1,
                ],
            ]
        );
        $this->mockBiz(
            'User:NotificationService',
            [
                [
                    'functionName' => 'notify',
                    'withParams' => [1, 'order-refund', $message],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($orderRefundService, 'notifyStudent', [$courseProduct]);
        $this->assertNull($result);

        $result = ReflectionUtils::invokeMethod($orderRefundService, 'notifyStudent', [$courseProduct]);
        $this->getNotificationService()->shouldHaveReceived('notify');
        $this->assertNull($result);
    }

    public function testNotifyAdmins()
    {
        $biz = $this->getBiz();
        $orderRefundService = $this->getOrderRefundService();
        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];
        $courseProduct->init(['targetId' => 1]);
        $message = [
            'type' => 'admin_operate',
            'targetId' => $courseProduct->targetId,
            'targetType' => $courseProduct->targetType,
            'title' => $courseProduct->title,
            'userId' => 1,
            'nickname' => 'admin',
        ];
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'searchUsers',
                    'returnValue' => [['id' => 11]],
                    'withParams' => [
                        ['roles' => 'ADMIN'],
                        ['id' => 'DESC'],
                        0,
                        PHP_INT_MAX,
                    ],
                ],
            ]
        );
        $this->mockBiz(
            'User:NotificationService',
            [
                [
                    'functionName' => 'notify',
                    'withParams' => [11, 'order-refund', $message],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($orderRefundService, 'notifyAdmins', [$courseProduct]);
        $this->getNotificationService()->shouldHaveReceived('notify');
        $this->assertNull($result);
    }

    protected function getOrderRefundService()
    {
        return $this->createService('OrderFacade:OrderRefundService');
    }

    protected function getWorkflowService()
    {
        return $this->biz->service('Order:WorkflowService');
    }

    protected function getNotificationService()
    {
        return $this->createService('User:NotificationService');
    }
}
