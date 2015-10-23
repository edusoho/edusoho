<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;

class OrderServiceTest extends BaseTestCase
{
    public function testSomeThing()
    {
        $this->assertNull(null);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getOrderService()
    {
        return $this->getServiceKernel()->createService('Course.CourseOrderService');
    }
}