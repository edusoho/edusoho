<?php
namespace Topxia\Service\Order\Tests;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class ClassroomOrderProcessorTest extends BaseTestCase
{
    public function testPreCheckWithBecomedStudent()
    {
        $user = $this->getCurrentUser();

        $course       = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $classroom    = array(
            'title'       => "testClassroom",
            'createdTime' => time()
        );
        $addClassroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($addClassroom['id'], array($createCourse['id']));
        $updateClassroom = $this->getClassroomService()->publishClassroom($addClassroom['id']);

        $normalUser = $this->createNormalUser();
        $current = new CurrentUser();
        $current->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($current);
        $this->getClassroomService()->becomeStudent($addClassroom['id'], $normalUser['id']);
        $processor = OrderProcessorFactory::create('classroom');
        $result    = $processor->preCheck($addClassroom['id'], $normalUser['id']);
        $this->assertEquals('已经是班级的学员了!', $result['error']);
    }

    public function testPreCheckWithUnableClassroom()
    {
        $user = $this->getCurrentUser();

        $course       = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $classroom    = array(
            'title'       => "testClassroom",
            'createdTime' => time()
        );
        $addClassroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($addClassroom['id'], array($createCourse['id']));
        $this->getClassroomService()->publishClassroom($addClassroom['id']);
        $unBuyableClassroom = $this->getClassroomService()->updateClassroom($addClassroom['id'], array('buyable' => 0));
        $normalUser         = $this->createNormalUser();
        $current = new CurrentUser();
        $current->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($current);
        $processor          = OrderProcessorFactory::create('classroom');
        $result             = $processor->preCheck($addClassroom['id'], $normalUser['id']);
        $this->assertEquals('该班级不可购买，如有需要，请联系客服', $result['error']);

    }

    public function testPreCheckWithUnpublishedClassroom()
    {
        $user = $this->getCurrentUser();

        $course       = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $classroom    = array(
            'title'       => "testClassroom",
            'createdTime' => time()
        );
        $addClassroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($addClassroom['id'], array($createCourse['id']));

        $normalUser = $this->createNormalUser();
        $current = new CurrentUser();
        $current->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($current);
        $processor  = OrderProcessorFactory::create('classroom');
        $result     = $processor->preCheck($addClassroom['id'], $normalUser['id']);
        $this->assertEquals('不能加入未发布班级!', $result['error']);
    }

    public function testPreCheckSuccess()
    {
        $user = $this->getCurrentUser();

        $course       = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $classroom    = array(
            'title'       => "testClassroom",
            'createdTime' => time()
        );
        $addClassroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($addClassroom['id'], array($createCourse['id']));
        $updateClassroom = $this->getClassroomService()->publishClassroom($addClassroom['id']);

        $normalUser = $this->createNormalUser();
        $current = new CurrentUser();
        $current->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($current);
        $processor  = OrderProcessorFactory::create('classroom');
        $result     = $processor->preCheck($addClassroom['id'], $normalUser['id']);
        $this->assertEquals(array(), $result);
    }

    public function testGetOrderInfoWithoutCoinEnable()
    {
        $user = $this->getCurrentUser();

        $course       = array(
            'title' => 'course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->setCoursePrice($createCourse['id'], 'default', 100.00);
        $classroom    = array(
            'title'       => "testClassroom",
            'createdTime' => time()
        );
        $addClassroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($addClassroom['id'], array($createCourse['id']));
        $updateClassroom = $this->getClassroomService()->publishClassroom($addClassroom['id']);

        $normalUser = $this->createNormalUser();
        $current = new CurrentUser();
        $current->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($current);
        $processor  = OrderProcessorFactory::create('classroom');
        $result     = $processor->getOrderInfo($addClassroom['id'], array());
        $this->assertEquals($result['totalPrice'], 0);
    }

    public function testGetOrderInfoWithCoinEnable()
    {
        $setting = array(
            'coin_name'           => '积分',
            'coin_picture'        => '',
            'coin_picture_50_50'  => '',
            'coin_picture_30_30'  => '',
            'coin_picture_20_20'  => '',
            'coin_picture_10_10'  => '',
            'cash_rate'           => '1',
            'coin_enabled'        => '1',
            'cash_model'          => 'deduction',
            'charge_coin_enabled' => '0',
            'coin_content'        => ''
        );
        $this->getSettingService()->set('coin', $setting);
        $user = $this->getCurrentUser();

        $course       = array(
            'title' => 'course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->setCoursePrice($createCourse['id'], 'default', 100.00);
        $classroom    = array(
            'title'       => "testClassroom",
            'createdTime' => time()
        );
        $addClassroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($addClassroom['id'], array($createCourse['id']));
        $updateClassroom = $this->getClassroomService()->publishClassroom($addClassroom['id']);

        $normalUser = $this->createNormalUser();
        $current = new CurrentUser();
        $current->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($current);
        $processor  = OrderProcessorFactory::create('classroom');
        $result     = $processor->getOrderInfo($addClassroom['id'], array());
        $this->assertEquals($result['totalPrice'], 0);

    }

    public function testShouldPayAmount()
    {
        $course       = array(
            'title' => 'course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->setCoursePrice($createCourse['id'], 'default', 100.00);
        $classroom    = array(
            'title'       => "testClassroom",
            'createdTime' => time()
        );
        $addClassroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($addClassroom['id'], array($createCourse['id']));
        $publishClassroom = $this->getClassroomService()->publishClassroom($addClassroom['id']);
        $updateClassroom  = $this->getClassroomService()->updateClassroom($addClassroom['id'], array('price' => '10'));

        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $processor = OrderProcessorFactory::create('classroom');
        list($amount, $totalPrice, $couponResult) = $processor->shouldPayAmount($addClassroom['id'], 'RMB', 1, 0, array('targetType' => 'classroom', 'totalPrice' => '10'));
        $this->assertEquals($amount, 10);
        $this->assertEquals($totalPrice, 10);
    }

    public function testGetNote()
    {
        $course       = array(
            'title' => 'onlinetestcourse1',
            'about' => '测试'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $processor    = OrderProcessorFactory::create('course');
        $note         = $processor->getNote(1);
        $this->assertEquals('测试', $note);

        $classroom = array(
            'title'      => 'test',
            'id'         => 1,
            'categoryId' => 1,
            'status'     => 'published'
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->updateClassroom(1, array('about' => '测试'));
        $processor = OrderProcessorFactory::create('classroom');
        $note      = $processor->getNote($classroom['id']);
        $this->assertEquals('测试', $note);

    }

    public function testGetTitle()
    {
        $course       = array(
            'title' => 'onlinetestcourse1',
            'about' => '测试'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $processor    = OrderProcessorFactory::create('course');
        $title        = $processor->getTitle(1);
        $this->assertEquals('onlinetestcourse1', $title);
        $classroom = array(
            'title'      => 'test',
            'id'         => 1,
            'categoryId' => 1,
            'status'     => 'published',
            'about'      => '测试班级'
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $processor = OrderProcessorFactory::create('classroom');
        $title     = $processor->getTitle($classroom['id']);
        $this->assertEquals('test', $title);

    }

    public function testCreateOrder()
    {
        $info        = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00', 'note' => '11', 'coupon' => '123', 'couponDiscount' => '0.0');

        $textClassroom = array(
            'title' => 'test'
        );
        $classroom     = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $processor = OrderProcessorFactory::create('classroom');
        $order     = $processor->createOrder($info, array('targetId' => $classroom['id'], 'targetType' => 'classroom'));
        $this->assertEquals($order['status'], 'created');
    }

    public function testDoPaySuccess()
    {
        $info        = array('targetId' => '1', 'payment' => 'coin', 'priceType' => 'RMB', 'totalPrice' => '0.00', 'coinRate' => '1', 'coinAmount' => '0.00', 'note' => '11', 'coupon' => '123', 'couponDiscount' => '0.0');
        $textClassroom = array(
            'title' => 'test'
        );
        $classroom     = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']); //publish
        $processor = OrderProcessorFactory::create('classroom');
        $order     = $processor->createOrder($info, array('targetId' => $classroom['id'], 'targetType' => 'classroom'));
        $result1   = $processor->doPaySuccess('success', $order);
        $result2   = $processor->doPaySuccess('', $order);
        $this->assertNull($result1);
        $this->assertNull($result2);
    }

    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->createService('System.SettingService');
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getCashAccountService()
    {
        return ServiceKernel::instance()->createService('Cash.CashAccountService');
    }

    protected function getClassroomOrderService()
    {
        return ServiceKernel::instance()->createService("Classroom:Classroom.ClassroomOrderService");
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
}
