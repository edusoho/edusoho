<?php
namespace Topxia\Service\Order\Tests;

// use Coupon\Service\Coupon\CouponService;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceException;

// use Topxia\Service\Taxonomy\TagService;

class OrderServiceTest extends BaseTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->getSettingService()->set('refund', array(
            'maxRefundDays'       => 0,
            'applyNotification'   => '您好，您退款的{{item}}，管理员已收到您的退款申请，请耐心等待退款审核结果。',
            'successNotification' => '您好，您申请退款的{{item}} 审核通过，将为您退款{{amount}}元。',
            'failedNotification'  => '您好，您申请退款的{{item}} 审核未通过，请与管理员再协商解决纠纷。'
        ));

    }

    public function testGetOrder()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        // print_r($createOrder);
        $result = $this->getOrderService()->getOrder($createOrder['id']);
        $this->assertEquals($result['id'], $createOrder['id']);
    }

    public function testGetOrderBySn()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        $result      = $this->getOrderService()->getOrderBySn($createOrder['sn']);
        $this->assertEquals($result['id'], $createOrder['id']);
    }

    public function testGetOrderByToken()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        $this->getOrderService()->updateOrder($createOrder['id'], array('token' => 'heepay_123'));
        $result = $this->getOrderService()->getOrderByToken('heepay_123');
        $this->assertEquals($result['id'], $createOrder['id']);
    }

    public function testFindOrdersBySns()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'none'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'none'
        );
        $createOrder1 = $this->getOrderService()->createOrder($order1);
        $createOrder2 = $this->getOrderService()->createOrder($order2);
        $sns          = array(
            $createOrder1['sn'],
            $createOrder2['sn']
        );
        $result = $this->getOrderService()->findOrdersBySns($sns);
        $this->assertCount(2, $result);
    }

    public function testFindOrdersByIds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'none'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'none'
        );
        $createOrder1 = $this->getOrderService()->createOrder($order1);
        $createOrder2 = $this->getOrderService()->createOrder($order2);
        $createIds    = array(
            $createOrder1['id'],
            $createOrder2['id']
        );
        $result = $this->getOrderService()->findOrdersByIds($createIds);
        $this->assertCount(2, $result);
    }

    public function testCreateOrderOnce()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $order = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        $result      = $this->getOrderService()->getOrder($createOrder['id']);
        // print_r($result);
        $this->assertEquals($createOrder['id'], $result['id']);

    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderTwice()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $incompleteOrder = array(
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $this->getOrderService()->createOrder($incompleteOrder);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderThird()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $noUserOrder = array(
            'userId'     => 100,
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $this->getOrderService()->createOrder($noUserOrder);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderFouth()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $errorPaymentOrder = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'test'
        );
        $this->getOrderService()->createOrder($errorPaymentOrder);
    }

    public function testCreateOrderFifth()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $testAmountOrder = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '0',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'alipay'
        );
        $result = $this->getOrderService()->createOrder($testAmountOrder);
        $this->assertEquals('none', $result['payment']);
    }

    // /**
    // * @expectedException Topxia\Service\Common\ServiceException
    // */
    // public function testCreateOrderSixth()
    // {
    //     $user = $this->createUser();
    //        $currentUser = new CurrentUser();
    //        $currentUser->fromArray($user);
    //        $this->getServiceKernel()->setCurrentUser($currentUser);
    //        $payment = array(
    //            'enabled' => 1,
    //            'disabled_message' => '尚未开启支付模块，无法购买课程。',
    //            'bank_gateway' => 'none',
    //            'alipay_enabled' => 0,
    //            'alipay_key' => '',
    //            'alipay_secret' => '',
    //            'alipay_account' => '',
    //            'alipay_type' => 'direct',
    //            'tenpay_enabled' => 1    ,
    //            'tenpay_key' => '',
    //            'tenpay_secret' => '',
    //        );
    //     $this->getSettingService()->set('payment', $payment);
    //     $course1 = array(
    //         'title' => 'course 1'
    //     );
    //     $course2 = array(
    //         'title' => 'course 1'
    //     );
    //     $createCourse1 = $this->getCourseService()->createCourse($course1);
    //     $createCourse2 = $this->getCourseService()->createCourse($course2);
    //     $couponData = array(
    //            'name' => 'test Coupon',
    //            'prefix' => 'prefixCoupon',
    //            'type' => 'minus',
    //            'rate' => 100,
    //            'generatedNum' => 10,
    //            'digits' => 8,
    //            'deadline' => date('Y-m-d',time() + 10*24*3600),
    //            'targetType' =>    'course'
    //        );
    //     $generateCoupon = $this->getCouponService()->generateCoupon($couponData);
    //     $findCouponsByBatchId = $this->getCouponService()->findCouponsByBatchId($generateCoupon['id'],0,5);
    //     $user = $this->createNormalUser();
    //     $currentUser = new CurrentUser();
    //        $currentUser->fromArray($user);
    //        $this->getServiceKernel()->setCurrentUser($currentUser);

    //        $testOrder1 = array(
    //            'userId' => $user['id'],
    //            'title' => 'buy course 1',
    //            'amount' => '100',
    //            'targetType' => 'course',
    //            'targetId' => $createCourse1['id'],
    //            'payment' => 'alipay',
    //            'couponCode' => $findCouponsByBatchId[1]['code']
    //        );
    //        $createOrder1 = $this->getOrderService()->createOrder($testOrder1);
    //        $this->getCouponService()->useCoupon($findCouponsByBatchId[1]['code'],$createOrder1);
    //        //这里省去了paycenter的步骤，直接将优惠码设为已用
    //        $testOrder2 = array(
    //            'userId' => $user['id'],
    //            'title' => 'buy course 2',
    //            'amount' => '100',
    //            'targetType' => 'course',
    //            'targetId' => $createCourse2['id'],
    //            'payment' => 'alipay',
    //            'couponCode' => $findCouponsByBatchId[1]['code']
    //        );
    //        $this->getOrderService()->createOrder($testOrder2);
    //        //如果在测试的时候此方法出错，可以优先排查是否安装了优惠吗插件依赖！！
    // }

    public function testSearchOrders()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 0.00,
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'none'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 0.00,
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'none'
        );
        $this->getOrderService()->createOrder($order1);
        $this->getOrderService()->createOrder($order2);
        $conditions = array("userId" => $user['id']);
        $result     = $this->getOrderService()->searchOrders($conditions, "early", 0, 5);
        $this->assertCount(2, $result);
        $result = $this->getOrderService()->searchOrders($conditions, 'latest', 0, 5);
        $this->assertCount(2, $result);
        $result = $this->getOrderService()->searchOrders($conditions, 'others', 0, 5);
        $this->assertCount(2, $result);

    }

    public function testSearchBill()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则paymen会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则paymen会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $order2 = $this->getOrderService()->createOrder($order2);

        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));

        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'paid', //账单的状态一定是支付完成的
            'userId'      => $user['id']
        );
        $sort   = 'latest';
        $result = $this->getOrderService()->searchBill($conditions, $sort, 0, 100);
        $this->assertEquals(count($result), 2);

    }

    public function testCountUserBillNum()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $order2 = $this->getOrderService()->createOrder($order2);

        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));

        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'paid', //账单的状态一定是支付完成的
            'userId'      => $user['id']
        );
        $sort   = 'latest';
        $result = $this->getOrderService()->countUserBillNum($conditions);
        $this->assertEquals($result, 2);
    }

    public function testSumOrderAmounts()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $courseIds     = array(
            $createCourse1['id'],
            $createCourse2['id']
        );
        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 0.00,
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'none',
            'status'     => 'paid'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 0.00,
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'none',
            'status'     => 'paid'
        );
        $createOrder1 = $this->getOrderService()->createOrder($order1);
        $createOrder2 = $this->getOrderService()->createOrder($order2);
        $payData1     = array(
            'sn'       => $createOrder1['sn'],
            'status'   => 'success',
            'amount'   => $createOrder1['amount'],
            'paidTime' => time()
        );
        $payData2 = array(
            'sn'       => $createOrder2['sn'],
            'status'   => 'success',
            'amount'   => $createOrder2['amount'],
            'paidTime' => time()
        );
        $this->getOrderService()->payOrder($payData1);
        $this->getOrderService()->payOrder($payData2);
        $startTime = strtotime('yesterday');
        $endTime   = strtotime('tomorrow');
        $result    = $this->getOrderService()->sumOrderAmounts($startTime, $endTime, $courseIds);
        // print_r($result);
        $this->assertCount(2, $result);

    }

    public function testSearchOrderCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 0.00,
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'none'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 0.00,
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'none'
        );
        $this->getOrderService()->createOrder($order1);
        $this->getOrderService()->createOrder($order2);
        $conditions = array("userId" => $user['id']);
        $result     = $this->getOrderService()->searchOrderCount($conditions);
        $this->assertEquals(2, $result);
    }

    public function testFindOrderLogsOnce()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        $logs        = $this->getOrderService()->createOrderLog($createOrder['id'], "pay_success", "支付成功。", $createOrder);
        $this->assertEquals($user['id'], $logs['userId']);
        $this->assertEquals($createOrder['id'], $logs['orderId']);
        $this->assertEquals('pay_success', $logs['type']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFildOrderLogsTwice()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        $logs        = $this->getOrderService()->createOrderLog(100, "pay_success", "支付成功。", array());
    }

    public function testPayOrderOnce()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $order = $this->getOrderService()->createOrder(array(
            'userId'     => $user['id'],
            'title'      => "testOrder",
            'targetType' => 'classroom',
            'targetId'   => $createCourse['id'],
            'amount'     => 10,
            'payment'    => 'none',
            'snPrefix'   => 'CR'
        ));

        $result = $this->getOrderService()->payOrder(array(
            'sn'       => $order['sn'],
            'status'   => 'success',
            'amount'   => $order['amount'],
            'paidTime' => time()
        ));
        $this->assertEquals($result[1]['title'], 'testOrder');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testPayOrderTwice()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $order = $this->getOrderService()->createOrder(array(
            'userId'     => $user['id'],
            'title'      => "testOrder",
            'targetType' => 'classroom',
            'targetId'   => $createCourse['id'],
            'amount'     => 10,
            'payment'    => 'none',
            'snPrefix'   => 'CR'
        ));

        $result = $this->getOrderService()->payOrder(array(
            'sn'       => $order['sn'].'1',
            'status'   => 'success',
            'amount'   => $order['amount'],
            'paidTime' => time()
        ));
    }

    public function testPayOrderThird()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $order = $this->getOrderService()->createOrder(array(
            'userId'     => $user['id'],
            'title'      => "testOrder",
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'amount'     => 100,
            'payment'    => 'none',
            'snPrefix'   => 'CR'
        ));

        $this->getOrderService()->payOrder(array(
            'sn'       => $order['sn'],
            'status'   => 'success',
            'amount'   => 100,
            'paidTime' => time()
        ));

    }

    public function testCanOrderPay()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->createNormalUser();
        $currentUser  = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $order = $this->getOrderService()->createOrder(array(
            'userId'     => $user['id'],
            'title'      => "testOrder",
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'amount'     => 10,
            'payment'    => 'none',
            'snPrefix'   => 'CR',
            'status'     => 'paid'
        ));
        $result = $this->getOrderService()->canOrderPay($order);
        $this->assertEquals(1, $result);
    }

    // /**
    // * @expectedException Topxia\Service\Common\ServiceException
    // */
    // public function testCanOrderPayTwice()
    // {
    //     $order = nulll;
    //     $this->getOrderService()->canOrderPay($order);
    // }

    public function testCancelOrder()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'alipay'
        );
        $order1        = $this->getOrderService()->createOrder($order1);
        $order2        = $this->getOrderService()->createOrder($order2);
        $canceledOrder = $this->getOrderService()->cancelOrder($order1['id']);
        $this->assertEquals($canceledOrder['status'], 'cancelled');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelOrderTwice()
    {
        $order  = array('id' => '100');
        $result = $this->getOrderService()->cancelOrder($order['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelOrderThird()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'tenpay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $result = $this->getOrderService()->cancelOrder($order1['id']);
    }

    public function testSumOrderPriceByTarget()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);

        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $result = $this->getOrderService()->sumOrderPriceByTarget('course', $createCourse1['id']);
        $this->assertEquals($result, '1');
    }

    public function testSumCouponDiscountByOrderIds()
    {
        //需要插件支持

    }

    public function testFindUserRefundCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $order2   = $this->getOrderService()->createOrder($order2);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));

        $this->getOrderService()->applyRefundOrder($payOrder[1]['id']);
        $this->getOrderService()->applyRefundOrder($payOrder2[1]['id']);
        $result = $this->getOrderService()->findUserRefundCount($user['id'], 0, 10);
        $this->assertEquals($result, 2);
    }

    public function testFindRefundsByIds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $order2   = $this->getOrderService()->createOrder($order2);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));

        $this->getOrderService()->applyRefundOrder($payOrder[1]['id']);
        $this->getOrderService()->applyRefundOrder($payOrder2[1]['id']);
        $result = $this->getOrderService()->findRefundsByIds(array(2, 1));
        $this->assertEquals(count($result), 2);
    }

    public function testFindUserRefunds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $order2   = $this->getOrderService()->createOrder($order2);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));

        $this->getOrderService()->applyRefundOrder($payOrder[1]['id']);
        $this->getOrderService()->applyRefundOrder($payOrder2[1]['id']);
        $result = $this->getOrderService()->findUserRefunds($user['id'], 0, 10);
        $this->assertEquals(count($result), 2);
    }

    public function testSearchRefunds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $order2   = $this->getOrderService()->createOrder($order2);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));
        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'success', //取消订单成功
            'userId'      => $user['id'],
            'amount'      => '0.1'
        );

        $this->getOrderService()->applyRefundOrder($payOrder[1]['id']);
        $this->getOrderService()->applyRefundOrder($payOrder2[1]['id']);
        $result = $this->getOrderService()->searchRefunds($conditions, '1', 0, 100);
        $this->assertEquals(count($result), 2);
    }

    public function testSearchRefundCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $order2   = $this->getOrderService()->createOrder($order2);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));
        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'success', //取消订单成功
            'userId'      => $user['id'],
            'amount'      => '0.1'
        );

        $this->getOrderService()->applyRefundOrder($payOrder[1]['id']);
        $this->getOrderService()->applyRefundOrder($payOrder2[1]['id']);
        $result = $this->getOrderService()->searchRefundCount($conditions);
        $this->assertEquals($result, 2);
    }

    public function testApplyRefundOrder()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $result = $this->getOrderService()->applyRefundOrder($payOrder[1]['id']);
        $this->assertEquals($result['status'], 'success');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testApplyRefundOrderTwice()
    {
        $order  = array('id' => '100');
        $result = $this->getOrderService()->applyRefundOrder($order['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testApplyRefundOrderThird()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'tenpay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $result = $this->getOrderService()->applyRefundOrder($order1['id']);
    }

    public function testAuditRefundOrder()
    {
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $refund = array(
            'maxRefundDays' => 100.00
        );
        $this->getSettingService()->set('refund', $refund);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();

        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $this->getOrderService()->applyRefundOrder($payOrder[1]['id'], 10);
        $result = $this->getOrderService()->auditRefundOrder($payOrder[1]['id'], 'true');
        $this->assertEquals('true', $result);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testAuditRefundOrderTwice()
    {
        $order  = array('id' => '100');
        $result = $this->getOrderService()->auditRefundOrder($order['id'], 'true');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testAuditRefundOrderThird()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $refund = array(
            'maxRefundDays' => 100.00
        );
        $this->getSettingService()->set('refund', $refund);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $normalUser    = new CurrentUser();
        $normalUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($normalUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'tenpay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $result = $this->getOrderService()->auditRefundOrder($order1['id'], 'true');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testAuditRefundOrderForth()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $refund = array(
            'maxRefundDays' => 100.00
        );
        $this->getSettingService()->set('refund', $refund);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $order1        = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'tenpay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        // $this->getOrderService()->applyRefundOrder($payOrder[1]['id'],1);
        $this->getOrderService()->auditRefundOrder($order1['id'], 'true');
    }

    // public function testAuditRefundOrderFifth()
    // {
    //     $user = $this->createUser();
    //     $currentUser = new CurrentUser();
    //     $currentUser->fromArray($user);
    //     $this->getServiceKernel()->setCurrentUser($currentUser);
    //     $payment = array(
    //         'enabled' => 1,
    //         'disabled_message' => '尚未开启支付模块，无法购买课程。',
    //         'bank_gateway' => 'none',
    //         'alipay_enabled' => 0,
    //         'alipay_key' => '',
    //         'alipay_secret' => '',
    //         'alipay_account' => '',
    //         'alipay_type' => 'direct',
    //         'tenpay_enabled' => 1,
    //         'tenpay_key' => '',
    //         'tenpay_secret' => '',
    //     );
    //     $this->getSettingService()->set('payment', $payment);
    //     $refund = array(
    //         'maxRefundDays' => 100.00,
    //         );
    //     $this->getSettingService()->set('refund',$refund);
    //     $course1 = array(
    //         'title' => 'course 1'
    //     );
    //     $createCourse1 = $this->getCourseService()->createCourse($course1);
    //     $order1 = array(
    //         'userId' => $user['id'],
    //         'title' => 'buy course 1',
    //         'amount' => 1.00, //价格一定要有,否则payment会变成none
    //         'targetType' => 'course',
    //         'targetId' => $createCourse1['id'],
    //         'payment' => 'tenpay'
    //     );
    //     $order1 = $this->getOrderService()->createOrder($order1);
    //     $payOrder = $this->getOrderService()->payOrder(array(
    //         'sn' => $order1['sn'],
    //         'status' => 'success',
    //         'amount' => $order1['amount'],
    //         'paidTime' => time(),
    //     ));
    // }

    public function testCancelRefundOrder()
    {
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $refund = array(
            'maxRefundDays' => 100.00
        );
        $this->getSettingService()->set('refund', $refund);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();

        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $this->getOrderService()->applyRefundOrder($payOrder[1]['id'], 10);
        $this->getOrderService()->cancelRefundOrder($payOrder[1]['id'], 'true');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelRefundOrderTwice()
    {
        $order  = array('id' => '100');
        $result = $this->getOrderService()->CancelRefundOrder($order['id'], 'true');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelRefundOrderThird()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $refund = array(
            'maxRefundDays' => 100.00
        );
        $this->getSettingService()->set('refund', $refund);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $normalUser    = new CurrentUser();
        $normalUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($normalUser);
        $order1 = array(
            'userId'     => $currentUser['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'tenpay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $this->getServiceKernel()->setCurrentUser($normalUser);
        $this->getOrderService()->applyRefundOrder($payOrder[1]['id'], 10);
        $this->getOrderService()->cancelRefundOrder($payOrder[1]['id'], 'true');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelRefundOrderForth()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $refund = array(
            'maxRefundDays' => 100.00
        );
        $this->getSettingService()->set('refund', $refund);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $normalUser    = new CurrentUser();
        $normalUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($normalUser);
        $order1 = array(
            'userId'     => $currentUser['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'tenpay'
        );

        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $this->getOrderService()->cancelRefundOrder($payOrder[0]['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelRefundOrderFifth()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $refund = array(
            'maxRefundDays' => 100.00
        );
        $this->getSettingService()->set('refund', $refund);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $normalUser    = new CurrentUser();
        $normalUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($normalUser);
        $order1 = array(
            'userId'     => $currentUser['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'tenpay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $this->getOrderService()->applyRefundOrder($payOrder[0]['id'], 10, 'sss');
        $this->getOrderService()->cancelRefundOrder($payOrder[0]['id']);
    }

    public function testAnalysisCourseOrderDataByTimeAndStatus()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $order2 = $this->getOrderService()->createOrder($order2);

        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));
        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'paid', //账单的状态一定是支付完成的
            'userId'      => $user['id'],
            'amount'      => '0.1'
        );
        $startTime = time() - 3600 * 24;
        $endTime   = time() + 3600 * 24;
        $status    = 'paid';
        $result    = $this->getOrderService()->AnalysisCourseOrderDataByTimeAndStatus($startTime, $endTime, $status);
        $this->assertEquals($result[0]['count'], '2');
    }

    public function testAnalysisPaidCourseOrderDataByTime()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $order2 = $this->getOrderService()->createOrder($order2);

        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));
        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'paid', //账单的状态一定是支付完成的
            'userId'      => $user['id'],
            'amount'      => '0.1'
        );
        $startTime = time() - 3600 * 24;
        $endTime   = time() + 3600 * 24;
        $result    = $this->getOrderService()->AnalysisPaidCourseOrderDataByTime($startTime, $endTime);
        $this->assertEquals($result[0]['count'], '2');
    }

    public function testAnalysisExitCourseDataByTimeAndStatus()
    {
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $refund = array(
            'maxRefundDays' => 100.00
        );
        $this->getSettingService()->set('refund', $refund);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse2['id'],
            'payment'    => 'alipay'
        );
        $order1   = $this->getOrderService()->createOrder($order1);
        $order2   = $this->getOrderService()->createOrder($order2);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));
        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));

        $this->getOrderService()->applyRefundOrder($payOrder[1]['id'], 10);
        $this->getOrderService()->applyRefundOrder($payOrder2[1]['id'], 10);
        $result    = $this->getOrderService()->auditRefundOrder($payOrder[1]['id'], 'true');
        $startTime = time() - 3600 * 24;
        $endTime   = time() + 3600 * 24;
        $result    = $this->getOrderService()->analysisExitCourseDataByTimeAndStatus($startTime, $endTime);
        $this->assertEquals($result[0]['count'], '2');
    }

    public function testAnalysisAmount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $order2 = $this->getOrderService()->createOrder($order2);

        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));
        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'paid', //账单的状态一定是支付完成的
            'userId'      => $user['id'],
            'amount'      => '0.1'
        );

        $result = $this->getOrderService()->analysisAmount($conditions);
        $this->assertEquals($result, '11');
    }

    public function testAnalysisAmountDataByTime()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $order2 = $this->getOrderService()->createOrder($order2);

        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));
        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'paid', //账单的状态一定是支付完成的
            'userId'      => $user['id'],
            'amount'      => '0.1'
        );
        $startTime = time() - 3600 * 24;
        $endTime   = time() + 3600 * 24;
        $result    = $this->getOrderService()->analysisAmountDataByTime($startTime, $endTime);
        $this->assertEquals($result[0]['count'], '11');
    }

    public function testAnalysisCourseAmountDataByTime()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $order2 = $this->getOrderService()->createOrder($order2);

        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order1['sn'],
            'status'   => 'success',
            'amount'   => $order1['amount'],
            'paidTime' => time()
        ));

        $payOrder2 = $this->getOrderService()->payOrder(array(
            'sn'       => $order2['sn'],
            'status'   => 'success',
            'amount'   => $order2['amount'],
            'paidTime' => time()
        ));
        $conditions = array(
            'keywordType' => 'nickname',
            'keyword'     => 'user',
            'endTime'     => time() + 3600 * 24, //账单的时间一定会比订单的时间迟
            'status'      => 'paid', //账单的状态一定是支付完成的
            'userId'      => $user['id'],
            'amount'      => '0.1'
        );
        $startTime = time() - 3600 * 24;
        $endTime   = time() + 3600 * 24;
        $result    = $this->getOrderService()->analysisCourseAmountDataByTime($startTime, $endTime);
        $this->assertEquals($result[0]['count'], '11');
    }

    public function testUpdateOrderCashSn()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $this->getOrderService()->updateOrderCashSn($order1['id'], '999999999');
        $result = $this->getOrderService()->getOrder($order1['id']);
        $this->assertEquals($result['cashSn'], '999999999');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateOrderCashSnTwice()
    {
        $order  = array('id' => '100');
        $result = $this->getOrderService()->updateOrderCashSn($order['id'], '100');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateOrderCashSnThird()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'tenpay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $result = $this->getOrderService()->updateOrderCashSn($order1['id'], null);
    }

    public function testCreatePayRecord()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1  = $this->getOrderService()->createOrder($order1);
        $payData = array('status' => 'closed');
        $this->getOrderService()->createPayRecord($order1['id'], $payData);
        $result = $this->getOrderService()->getOrder($order1['id']);
        $this->assertEquals($result['data'], $payData);
    }

    public function testCreateOrderLog()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled'          => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway'     => 'none',
            'alipay_enabled'   => 0,
            'alipay_key'       => '',
            'alipay_secret'    => '',
            'alipay_account'   => '',
            'alipay_type'      => 'direct',
            'tenpay_enabled'   => 1,
            'tenpay_key'       => '',
            'tenpay_secret'    => ''
        );
        $this->getSettingService()->set('payment', $payment);
        $course1 = array(
            'title' => 'course 1'
        );
        $course2 = array(
            'title' => 'course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => 1.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order2 = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 2',
            'amount'     => 10.00, //价格一定要有,否则payment会变成none
            'targetType' => 'course',
            'targetId'   => $createCourse1['id'],
            'payment'    => 'alipay'
        );
        $order1 = $this->getOrderService()->createOrder($order1);
        $order2 = $this->getOrderService()->createOrder($order2);
        $result = $this->getOrderService()->createOrderLog($order1['id'], 'ssfs');
        $this->assertEquals($result['id'], 3); //创建order会增加一条orderlog,这里直接增加一条Log,没有增加order
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderLogTwice()
    {
        $order  = array('id' => '100');
        $result = $this->getOrderService()->createOrderLog($order['id'], 'ssfs');
    }

    //=================私有或者受保护的方法，用来调用命名空间外的对象[start]==============
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function createUser()
    {
        $user              = array();
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;

    }

    private function createNormalUser()
    {
        $user              = array();
        $user['email']     = "normal@user.com";
        $user['nickname']  = "normal";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER');
        return $user;
    }

    //=================私有或者受保护的方法，用来调用命名空间外的类核对象[e n d]==============
}
