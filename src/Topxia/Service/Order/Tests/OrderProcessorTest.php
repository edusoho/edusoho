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
            'status'     => 'published',
            'about'      => '测试班级'
        );
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $processor = OrderProcessorFactory::create('classroom');
        $note      = $processor->getNote(1);
        $this->assertEquals('测试班级', $note);

        $level = array(
            'name'        => 'vip',
            'description' => '测试vip'
        );
        $level     = $this->getLevelService()->createLevel($level);
        $processor = OrderProcessorFactory::create('vip');
        $note      = $processor->getNote(1);
        $this->assertEquals('测试vip', $note);
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
        $title     = $processor->getTitle(1);
        $this->assertEquals('test', $title);

        $level = array(
            'name'        => 'vip',
            'description' => '测试vip'
        );
        $level     = $this->getLevelService()->createLevel($level);
        $processor = OrderProcessorFactory::create('vip');
        $title     = $processor->getTitle(1);
        $this->assertEquals('vip', $title);
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
}
