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
        $this->getOrderService()->createOrder($order);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderNoTarget()
    {
        $this->normalLogin();

        $order = array('');
        $this->getOrderService()->createOrder($order);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateOrderNoCourse()
    {
     
        $this->normalLogin();
        $order = array('targetId'>=1,'payment'=>'alipay');
        $this->getOrderService()->createOrder($order);
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
        $createdOrder = $this->getOrderService()->createOrder($order);
        $this->assertEquals($createdOrder['targetId'], $order['targetId']);
        $this->assertEquals($createdOrder['payment'], $order['payment']);
        $this->assertEquals($createdOrder['totalPrice'], $order['totalPrice']);
        $this->assertEquals($createdOrder['priceType'], $order['priceType']);
        $this->assertEquals($createdOrder['coinRate'], $order['coinRate']);
        $this->assertEquals($createdOrder['coinAmount'], $order['coinAmount']);
        $this->assertEquals($createdOrder['status'], 'paid');

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

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
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