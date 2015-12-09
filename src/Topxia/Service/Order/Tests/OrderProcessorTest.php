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

}
