<?php

namespace Classroom\Service\Classroom\Tests;

use Topxia\Service\Common\BaseTestCase;

class ClassroomOrderServiceTest extends BaseTestCase
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

    public function testCreateOrder()
    {
        $info          = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00', 'note' => '11', 'coupon' => '123', 'couponDiscount' => '0.0');
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $order = $this->getClassroomOrderService()->createOrder($info);
        $this->assertEquals($order['status'], 'created');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderWithEmptyInfo()
    {
        $info = array();
        $this->getClassroomOrderService()->createOrder($info);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderWithIsStudent()
    {
        $info = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00');

        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $this->getClassroomOrderService()->createOrder($info);
        $this->getClassroomOrderService()->createOrder($info);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderWithEmptyClassroom()
    {
        $info = array('targetId' => '100', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00');

        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $this->getClassroomOrderService()->createOrder($info);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderWithEmptyCantBuyClassroom()
    {
        $info          = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00');
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $fields = array('buyable' => '0');
        $this->getClassroomService()->updateClassroom($classroom['id'], $fields); //封闭班级
        $this->getClassroomOrderService()->createOrder($info);
    }

    public function testDoSuccessPayOrder()
    {
        $info          = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00', 'note' => '11', 'coupon' => '123', 'couponDiscount' => '0.0');
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $order = $this->getClassroomOrderService()->createOrder($info);
        $this->getClassroomOrderService()->doSuccessPayOrder($order['id']);
        $orderLog = $this->getOrderService()->findOrderLogs($order['id']);
        $this->assertEquals(count($orderLog), '1');
    }

    public function testDoSuccessPayOrderTwice()
    {
        $info          = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '1.00', 'coinRate' => '1', 'coinAmount' => '0.00', 'note' => '11', 'coupon' => '123', 'couponDiscount' => '0.0');
        $user          = $this->getCurrentUser();
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $order = $this->getClassroomOrderService()->createOrder($info);

        $isClassroomStudent = $this->getClassroomService()->isClassroomStudent($classroom['id'], $user['id']);
        $this->assertEquals(false, $isClassroomStudent);
        $this->getClassroomOrderService()->doSuccessPayOrder($order['id']);
        $isClassroomStudent = $this->getClassroomService()->isClassroomStudent($classroom['id'], $user['id']);
        $this->assertEquals(true, $isClassroomStudent);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testDoSuccessPayOrderNotClassroomOrder()
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
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $user         = $this->getCurrentUser();
        $order        = array(
            'userId'     => $user['id'],
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        $this->getClassroomOrderService()->doSuccessPayOrder($createOrder['id']);
    }

    public function testApplyRefundOrder()
    {
        $info          = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'coin', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00', 'note' => '11', 'coupon' => '123', 'couponDiscount' => '0');
        $textClassroom = array(
            'title' => 'test',
            'price' => '200'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $order    = $this->getClassroomOrderService()->createOrder($info);
        $payOrder = $this->getOrderService()->payOrder(array(
            'sn'       => $order['sn'],
            'status'   => 'success',
            'amount'   => $order['amount'],
            'paidTime' => time()
        ));
        $this->getClassroomService()->becomeStudent($order['targetId'], $order['userId'], $info);
        $refund = $this->getClassroomOrderService()->applyRefundOrder($order['id'], "a", "我要外卖啊", "a");
        $this->assertEquals($refund['status'], 'success');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testApplyRefundOrderWithEmptyOrder()
    {
        $order = array('id' => '100');
        $this->getClassroomOrderService()->applyRefundOrder($order['id'], "a", "我要外卖啊", "a");
    }

    public function getOrder()
    {
        $info          = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00', 'note' => '11', 'coupon' => '123', 'couponDiscount' => '0.0');
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $order  = $this->getClassroomOrderService()->createOrder($info);
        $result = $this->getClassroomOrderService()->getOrder($order['id']);
        $this->assertEquals($order['status'], $result['status']);
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function getClassroomOrderService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomOrderService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
