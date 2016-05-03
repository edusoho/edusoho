<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;
use Topxia\Service\User\CurrentUser;

class OrderServiceTest extends BaseTestCase
{
    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderNoLogin()
    {
        $order = array('');
        $this->getCourseOrderService()->createOrder($order);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderNoTarget()
    {
        $this->normalLogin();

        $order = array('');
        $this->getCourseOrderService()->createOrder($order);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderNoCourse()
    {
     
        $this->normalLogin();
        $order = array('targetId'>=1,'payment'=>'alipay');
        $this->getCourseOrderService()->createOrder($order);
    }

    public function testCreateOrder()
    {

        $this->teacherLogin();
        $course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1'));

        $this->getCourseService()->publishCourse($course1['id']);

        $this->normalLogin();
        $order = array(
            'targetId' => $course1['id'],
            'payment' => 'none',
            'totalPrice' => 0,
            'priceType' =>'RMB',
            'coinRate' => 1,
            'coinAmount' => 0
        );
        $createdOrder = $this->getCourseOrderService()->createOrder($order);
        $this->assertEquals($createdOrder['targetId'], $order['targetId']);
        $this->assertEquals($createdOrder['payment'], $order['payment']);
        $this->assertEquals($createdOrder['totalPrice'], $order['totalPrice']);
        $this->assertEquals($createdOrder['priceType'], $order['priceType']);
        $this->assertEquals($createdOrder['coinRate'], $order['coinRate']);
        $this->assertEquals($createdOrder['coinAmount'], $order['coinAmount']);
        $this->assertEquals($createdOrder['status'], 'paid');

    }

    public function testUpdateOrder()
    {
        $this->teacherLogin();
        $course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1'));

        $this->getCourseService()->publishCourse($course1['id']);

        $this->normalLogin();
        $order = array(
            'targetId' => $course1['id'],
            'payment' => 'none',
            'totalPrice' => 10,
            'priceType' =>'RMB',
            'coinRate' => 1,
            'coinAmount' => 0
        );
        $createdOrder = $this->getCourseOrderService()->createOrder($order);

        $updatedOrder = $this->getCourseOrderService()->updateOrder($createdOrder['id'],array(
            'payment' => 'alipay',
            'totalPrice' => 12,
            'priceType' =>'RMB',
            'coinRate' => 1,
            'coinAmount' => 1,
            'amount' => 1,
            'userId' =>$createdOrder['userId'],
            'courseId' => $course1['id'],
        ));

        $this->assertEquals('alipay',$updatedOrder['payment']);
        $this->assertEquals(12,$updatedOrder['totalPrice']);
        $this->assertEquals('RMB',$updatedOrder['priceType']);
        $this->assertEquals(1,$updatedOrder['coinRate']);
        $this->assertEquals(1,$updatedOrder['coinAmount']);

    }

    public function testCancelOrder()
    {
        $this->teacherLogin();
        $course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1'));

        $this->getCourseService()->publishCourse($course1['id']);

        $this->normalLogin();
        $order = array(
            'targetId' => $course1['id'],
            'payment' => 'none',
            'totalPrice' => 10,
            'priceType' =>'RMB',
            'coinRate' => 1,
            'coinAmount' => 1
        );
        $createdOrder = $this->getCourseOrderService()->createOrder($order);
        $this->getCourseOrderService()->cancelOrder($createdOrder['id']);

        $order = $this->getOrderService()->getOrder($createdOrder['id']);

        $this->assertEquals('cancelled', $order['status']);
    }



    protected function normalLogin()
    {
        $user1       = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user1);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        return $currentUser;
    }

    protected function teacherLogin()
    {
        $user1       = $this->createTeacherUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user1);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        return $currentUser;
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCourseOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
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

    private function createTeacherUser()
    {
        $user              = array();
        $user['email']     = "teacherUser@user.com";
        $user['nickname']  = "teacherUser";
        $user['password']  = "teacherUser";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_TEACHER');
        return $user;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}