<?php
namespace Topxia\Service\Order\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Order\OrderProcessor\OrderProcessorFactory;

class OrderProcessorTest extends BaseTestCase
{
    public function testGetNote()
    {
        $course = array(
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

        $vip       = array("name" => "测试", "monthPrice" => 0.01, "yearPrice" => 0.01, "description" => "测试");
        $vip       = $this->getLevelService()->createLevel($vip);
        $processor = OrderProcessorFactory::create('vip');
        $note      = $processor->getNote($vip['id']);
        $this->assertEquals('测试', $note);

        $this->setSettingcoin();
        $processor = OrderProcessorFactory::create('coin');
        $note      = $processor->getNote(1);
        $this->assertEquals('充值coin', $note);
    }

    public function testGetTitle()
    {
        $course = array(
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

        $vip       = array("name" => "测试", "monthPrice" => 0.01, "yearPrice" => 0.01, "description" => "测试");
        $vip       = $this->getLevelService()->createLevel($vip);
        $processor = OrderProcessorFactory::create('vip');
        $note      = $processor->getTitle($vip['id']);
        $this->assertEquals('测试', $note);

        $this->setSettingcoin();
        $processor = OrderProcessorFactory::create('coin');
        $note      = $processor->getTitle(1);
        $this->assertEquals('coin', $note);

    }

    public function testGetOrderBySn()
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
        $vip = array(
            'enabled'                    => 1,
            'buyType'                    => 10,
            'default_buy_months10'       => 3,
            'default_buy_years10'        => 1,
            'default_buy_years'          => 1,
            'default_buy_months'         => 1,
            'upgrade_min_day'            => 30,
            'deadlineNotify'             => 0,
            'daysOfNotifyBeforeDeadline' => 10,
            'poster'                     => 0,
            'poster_bgcolor'             => '#f13b54'
        );

        $this->getSettingService()->set('vip', $vip);
        $course = array(
            'title' => 'course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $order        = array(
            'userId'     => 1,
            'title'      => 'buy course 1',
            'amount'     => '100',
            'targetType' => 'course',
            'targetId'   => $createCourse['id'],
            'payment'    => 'none'
        );
        $order     = $this->getOrderService()->createOrder($order);
        $processor = OrderProcessorFactory::create('course');
        $result    = $processor->getOrderBySn($order['sn']);
        $this->assertEquals('course', $order['targetType']);

        $classroom = array(
            'title'      => 'test',
            'id'         => 1,
            'categoryId' => 1,
            'status'     => 'published',
            'about'      => '测试班级'
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $order     = $this->getOrderService()->createOrder(array(
            'userId'     => 1,
            'title'      => "testOrder",
            'targetType' => 'classroom',
            'targetId'   => $createCourse['id'],
            'amount'     => 10,
            'payment'    => 'none',
            'snPrefix'   => 'CR'
        ));
        $processor = OrderProcessorFactory::create('classroom');
        $result    = $processor->getOrderBySn($order['sn']);
        $this->assertEquals('classroom', $order['targetType']);

        $vip       = array("name" => "测试", "monthPrice" => 0.01, "yearPrice" => 0.01, "description" => "测试");
        $vip       = $this->getLevelService()->createLevel($vip);
        $processor = OrderProcessorFactory::create('vip');
        $order     = $this->getOrderService()->createOrder(array(
            'userId'     => 1,
            'title'      => "testOrder",
            'targetType' => 'vip',
            'targetId'   => $vip['id'],
            'amount'     => 10,
            'payment'    => 'none',
            'snPrefix'   => 'CR'
        ));

        $result = $processor->getOrderBySn($order['sn']);
        $this->assertEquals('vip', $order['targetType']);

        $this->setSettingcoin();
        $order = array(
            'status'      => 'created',
            'amount'      => '100.00',
            'payment'     => 'none',
            'note'        => 'hello',
            'userId'      => '1',
            'createdTime' => time()
        );
        $order     = $this->getCashOrdersService()->addOrder($order);
        $processor = OrderProcessorFactory::create('coin');
        $result    = $processor->getOrderBySn($order['sn']);
        $this->assertEquals('100.00', $order['amount']);
    }

    private function setSettingcoin()
    {
        $coinSettingsPosted = array(
            'cash_rate' => '1.0',
            'coin_name' => 'coin'
        );
        $this->getSettingService()->set('coin', $coinSettingsPosted);

    }

    protected function getCourseService()
    {
        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

    protected function getCashOrdersService()
    {
        return $this->getServiceKernel()->createService('Cash.CashOrdersService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Order.OrderService');
    }
}
