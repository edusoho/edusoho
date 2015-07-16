<?php
namespace Topxia\Service\Order\Tests;

use Coupon\Service\Coupon\CouponService;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Order\OrderService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\System\SettingService;
use Topxia\Service\Course\CourseService;
// use Topxia\Service\Taxonomy\TagService;


class OrderServiceTest extends BaseTestCase
{

	public function testGetOrder()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
        );
		$this->getSettingService()->set('payment', $payment);
		$course = array(
			'title' => 'course 1'
		);
		$createCourse = $this->getCourseService()->createCourse($course);
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order = array(
        	'userId' => $user['id'],
        	'title' => 'buy course 1',  
        	'amount' => '100', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse['id'], 
        	'payment' => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        // print_r($createOrder);
        $result = $this->getOrderService()->getOrder($createOrder['id']);
        $this->assertEquals($result['id'],$createOrder['id']);
	}

	public function testGetOrderBySn()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
        );
		$this->getSettingService()->set('payment', $payment);
		$course = array(
			'title' => 'course 1'
		);
		$createCourse = $this->getCourseService()->createCourse($course);
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order = array(
        	'userId' => $user['id'],
        	'title' => 'buy course 1',  
        	'amount' => '100', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse['id'], 
        	'payment' => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        $result = $this->getOrderService()->getOrderBySn($createOrder['sn']);
        $this->assertEquals($result['id'],$createOrder['id']);
	}

	public function testFindOrdersByIds()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
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
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $order1 = array(
        	'userId' => $user['id'],
        	'title' => 'buy course 1',  
        	'amount' => '100', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse1['id'], 
        	'payment' => 'none'
        );
        $order2 = array(
        	'userId' => $user['id'],
        	'title' => 'buy course 2',  
        	'amount' => '100', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse2['id'], 
        	'payment' => 'none'
        );
        $createOrder1 = $this->getOrderService()->createOrder($order1);
        $createOrder2 = $this->getOrderService()->createOrder($order2);
        $createIds = array(
        	$createOrder1['id'],
        	$createOrder2['id']
        );
        $result = $this->getOrderService()->findOrdersByIds($createIds);
        $this->assertCount(2,$result);
	}

	
	public function testCreateOrderOnce()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
        );
		$this->getSettingService()->set('payment', $payment);
		$course = array(
			'title' => 'course 1'
		);
		$createCourse = $this->getCourseService()->createCourse($course);
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $order = array(
        	'userId' => $user['id'],
        	'title' => 'buy course 1',  
        	'amount' => '100', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse['id'], 
        	'payment' => 'none'
        );
        $createOrder = $this->getOrderService()->createOrder($order);
        $result = $this->getOrderService()->getOrder($createOrder['id']);
        // print_r($result);
        $this->assertEquals($createOrder['id'],$result['id']);


	}

	/**  
	* @expectedException Topxia\Service\Common\ServiceException  
	*/
	public function testCreateOrderTwice()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
        );
		$this->getSettingService()->set('payment', $payment);
		$course = array(
			'title' => 'course 1'
		);
		$createCourse = $this->getCourseService()->createCourse($course);
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $incompleteOrder = array(
        	'title' => 'buy course 1',  
        	'amount' => '100', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse['id'], 
        	'payment' => 'none'
        );
        $this->getOrderService()->createOrder($incompleteOrder);
	}

	/**  
	* @expectedException Topxia\Service\Common\ServiceException  
	*/
	public function testCreateOrderThird()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
        );
		$this->getSettingService()->set('payment', $payment);
		$course = array(
			'title' => 'course 1'
		);
		$createCourse = $this->getCourseService()->createCourse($course);
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $noUserOrder = array(
        	'userId' => 100,
        	'title' => 'buy course 1',  
        	'amount' => '100', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse['id'], 
        	'payment' => 'none'
        );
        $this->getOrderService()->createOrder($noUserOrder);
	}

	/**  
	* @expectedException Topxia\Service\Common\ServiceException  
	*/
	public function testCreateOrderFouth()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
        );
		$this->getSettingService()->set('payment', $payment);
		$course = array(
			'title' => 'course 1'
		);
		$createCourse = $this->getCourseService()->createCourse($course);
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $errorPaymentOrder = array(
        	'userId' => $user['id'],
        	'title' => 'buy course 1',  
        	'amount' => '100', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse['id'], 
        	'payment' => 'test'
        );
        $this->getOrderService()->createOrder($errorPaymentOrder);
	}

	public function testCreateOrderFifth()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
        );
		$this->getSettingService()->set('payment', $payment);
		$course = array(
			'title' => 'course 1'
		);
		$createCourse = $this->getCourseService()->createCourse($course);
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $testAmountOrder = array(
        	'userId' => $user['id'],
        	'title' => 'buy course 1',  
        	'amount' => '0', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse['id'], 
        	'payment' => 'alipay'
        );
        $result = $this->getOrderService()->createOrder($testAmountOrder);
        $this->assertEquals('none',$result['payment']);
	}

	public function testCreateOrderSixth()
	{
		$user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $payment = array(
            'enabled' => 1,
            'disabled_message' => '尚未开启支付模块，无法购买课程。',
            'bank_gateway' => 'none',
            'alipay_enabled' => 0,
            'alipay_key' => '',
            'alipay_secret' => '',
            'alipay_account' => '',
            'alipay_type' => 'direct',
            'tenpay_enabled' => 1	,
            'tenpay_key' => '',
            'tenpay_secret' => '',
        );
		$this->getSettingService()->set('payment', $payment);
		$course = array(
			'title' => 'course 1'
		);
		$createCourse = $this->getCourseService()->createCourse($course);
		// $this->
		$user = $this->createNormalUser();
		$currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $testAmountOrder = array(
        	'userId' => $user['id'],
        	'title' => 'buy course 1',  
        	'amount' => '0', 
        	'targetType' => 'course', 
        	'targetId' => $createCourse['id'], 
        	'payment' => 'alipay'
        );
        $result = $this->getOrderService()->createOrder($testAmountOrder);
        $this->assertEquals('none',$result['payment']);
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
    protected function getCouponService()
    {
        return $this->getServiceKernel()->createService('Coupon:Coupon.CouponService');
    }

    private function createUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        return $user;

    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = "normal@user.com";
        $user['nickname'] = "normal";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');
        return $user;
    }
    //=================私有或者受保护的方法，用来调用命名空间外的类核对象[e n d]==============
}