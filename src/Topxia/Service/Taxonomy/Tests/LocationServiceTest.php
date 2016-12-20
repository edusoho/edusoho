<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Taxonomy\LocationService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class LocationServiceTest extends BaseTestCase
{   
    public function testLocationXXX()
    {
       $this->assertNull(null);
    }

    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course:MaterialService');
    }
}