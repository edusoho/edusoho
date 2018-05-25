<?php

namespace Tests\Unit\OrderFacade\Service;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Biz\OrderFacade\Product\CourseProduct;

class OrderRefundServiceTest extends BaseTestCase
{
    public function testSearchRefunds()
    {
        $this->mockBiz(
            'Order:OrderRefundService',
            array(
                array(
                    'functionName' => 'searchRefunds',
                    'returnValue' => array(array('id' => 11, 'order_id' => 111)),
                    'withParams' => array(array('order_id' => 111), array(), 0, 5),
                ),
            )
        );
        $result = $this->getOrderRefundService()->searchRefunds(array('order_id' => 111), array(), 0, 5);
        $this->assertEquals(array(array('id' => 11, 'order_id' => 111)), $result);
    }

    public function testCountRefunds()
    {
        $this->mockBiz(
            'Order:OrderRefundService',
            array(
                array(
                    'functionName' => 'countRefunds',
                    'returnValue' => 5,
                    'withParams' => array(array('order_id' => 111)),
                ),
            )
        );
        $result = $this->getOrderRefundService()->countRefunds(array('order_id' => 111));
        $this->assertEquals(5, $result);
    }

    public function testGetOrderRefundById()
    {
        $this->mockBiz(
            'Order:OrderRefundService',
            array(
                array(
                    'functionName' => 'getOrderRefundById',
                    'returnValue' => array('id' => 11, 'order_id' => 111),
                    'withParams' => array(11),
                ),
            )
        );
        $result = $this->getOrderRefundService()->getOrderRefundById(11);
        $this->assertEquals(array('id' => 11, 'order_id' => 111), $result);
    }

    public function testApplyOrderRefund()
    {
        $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'getOrder',
                    'returnValue' => array('id' => 11, 'order_id' => 111, 'created_user_id' => 1, 'pay_amount' => 10, 'refund_deadline' => time() + 1000),
                    'withParams' => array(11),
                ),
                array(
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => array(array('id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course')),
                    'withParams' => array(11),
                ),
            )
        );
        $this->mockBiz(
            'Order:OrderRefundService',
            array(
                array(
                    'functionName' => 'searchRefunds',
                    'returnValue' => array(),
                    'withParams' => array(array('order_id' => 11, 'status' => 'auditing'), array(), 0, PHP_INT_MAX),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'searchRefunds',
                    'returnValue' => array(array('id' => 11, 'order_id' => 11)),
                    'withParams' => array(array('order_id' => 11, 'status' => 'auditing'), array(), 0, PHP_INT_MAX),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'Order:WorkflowService',
            array(
                array(
                    'functionName' => 'applyOrderRefund',
                    'returnValue' => array('id' => 11, 'order_id' => 111),
                    'withParams' => array(11, array('reason' => 'test')),
                ),
            )
        );
        $result = $this->getOrderRefundService()->applyOrderRefund(11, array('reason' => 'test'));
        $this->assertEquals(array('id' => 11, 'order_id' => 111), $result);

        $result = $this->getOrderRefundService()->applyOrderRefund(11, array('reason' => 'test'));
        $this->assertEquals(array('id' => 11, 'order_id' => 11), $result);
    }

    public function testRefuseRefund()
    {
        $currentUser = $this->getCurrentUser();
        $currentUser->setPermissions(array('admin' => 1));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'getOrder',
                    'returnValue' => array('id' => 11, 'order_id' => 111, 'created_user_id' => 1, 'pay_amount' => 10, 'refund_deadline' => time() + 1000, 'user_id' => 1),
                    'withParams' => array(11),
                ),
                array(
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => array(array('id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course', 'refund_id' => 11)),
                    'withParams' => array(11),
                ),
            )
        );
        $this->mockBiz(
            'Order:WorkflowService',
            array(
                array(
                    'functionName' => 'refuseRefund',
                    'withParams' => array(11, array()),
                ),
            )
        );
        $result = $this->getOrderRefundService()->refuseRefund(11, array());
        $this->getWorkflowService()->shouldHaveReceived('refuseRefund');
        $this->assertEquals(111, $result->targetId);
    }

    public function testAdoptRefund()
    {
        $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'getOrder',
                    'returnValue' => array('id' => 11, 'order_id' => 111, 'created_user_id' => 1, 'pay_amount' => 10, 'refund_deadline' => time() + 1000, 'user_id' => 1),
                    'withParams' => array(11),
                ),
                array(
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => array(array('id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course', 'refund_id' => 11)),
                    'withParams' => array(11),
                ),
            )
        );
        $this->mockBiz(
            'Order:WorkflowService',
            array(
                array(
                    'functionName' => 'adoptRefund',
                    'withParams' => array(11, array()),
                ),
            )
        );
        $result = $this->getOrderRefundService()->adoptRefund(11, array());
        $this->getWorkflowService()->shouldHaveReceived('adoptRefund');
        $this->assertEquals(111, $result->targetId);
    }

    public function testCancelRefund()
    {
        $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'getOrder',
                    'returnValue' => array('id' => 11, 'order_id' => 111, 'created_user_id' => 1, 'pay_amount' => 10, 'refund_deadline' => time() + 1000, 'user_id' => 1),
                    'withParams' => array(11),
                ),
                array(
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => array(array('id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course', 'refund_id' => 11)),
                    'withParams' => array(11),
                ),
            )
        );
        $this->mockBiz(
            'Order:WorkflowService',
            array(
                array(
                    'functionName' => 'cancelRefund',
                    'withParams' => array(11),
                ),
            )
        );
        $result = $this->getOrderRefundService()->cancelRefund(11);
        $this->getWorkflowService()->shouldHaveReceived('cancelRefund');
        $this->assertNull($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testTryManageOrderRefund()
    {
        $orderRefundService = $this->getOrderRefundService();
        $currentUser = $this->getCurrentUser();
        $currentUser->setPermissions(array());
        $this->getServiceKernel()->setCurrentUser($currentUser);
        ReflectionUtils::invokeMethod($orderRefundService, 'tryManageOrderRefund');
    }

    public function testGetProductAndOrderItem()
    {
        $orderRefundService = $this->getOrderRefundService();
        $this->mockBiz(
            'Order:OrderService',
            array(
                array(
                    'functionName' => 'findOrderItemsByOrderId',
                    'returnValue' => array(array('id' => 11, 'title' => 'title', 'target_id' => 111, 'target_type' => 'course', 'refund_id' => 11)),
                    'withParams' => array(11),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($orderRefundService, 'getProductAndOrderItem', array(array('id' => 11)));
        $this->assertEquals(11, $result[1]['id']);
    }

    public function testNotifyStudent()
    {
        $biz = $this->getBiz();
        $orderRefundService = $this->getOrderRefundService();
        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];
        $courseProduct->init(array('targetId' => 1));
        $message = array(
            'type' => 'apply_create',
            'targetId' => $courseProduct->targetId,
            'targetType' => $courseProduct->targetType,
            'title' => $courseProduct->title,
            'userId' => 1,
            'nickname' => 'admin',
        );
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(),
                    'withParams' => array('refund', array()),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('applyNotification' => '申请审核'),
                    'withParams' => array('refund', array()),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'User:NotificationService',
            array(
                array(
                    'functionName' => 'notify',
                    'withParams' => array(1, 'order-refund', $message),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($orderRefundService, 'notifyStudent', array($courseProduct));
        $this->assertNull($result);

        $result = ReflectionUtils::invokeMethod($orderRefundService, 'notifyStudent', array($courseProduct));
        $this->getNotificationService()->shouldHaveReceived('notify');
        $this->assertNull($result);
    }

    public function testNotifyAdmins()
    {
        $biz = $this->getBiz();
        $orderRefundService = $this->getOrderRefundService();
        $courseProduct = $biz['order.product.'.CourseProduct::TYPE];
        $courseProduct->init(array('targetId' => 1));
        $message = array(
            'type' => 'admin_operate',
            'targetId' => $courseProduct->targetId,
            'targetType' => $courseProduct->targetType,
            'title' => $courseProduct->title,
            'userId' => 1,
            'nickname' => 'admin',
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'searchUsers',
                    'returnValue' => array(array('id' => 11)),
                    'withParams' => array(
                        array('roles' => 'ADMIN'),
                        array('id' => 'DESC'),
                        0,
                        PHP_INT_MAX,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'User:NotificationService',
            array(
                array(
                    'functionName' => 'notify',
                    'withParams' => array(11, 'order-refund', $message),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($orderRefundService, 'notifyAdmins', array($courseProduct));
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
