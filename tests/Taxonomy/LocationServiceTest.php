<?php
namespace  Tests\Taxonomy;

use Codeages\Biz\Framework\UnitTests\BaseTestCase;

class LocationServiceTest extends BaseTestCase
{   
    public function testLocationXXX()
    {
       $this->assertNull(null);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}