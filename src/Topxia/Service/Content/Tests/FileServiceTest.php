<?php
namespace Topxia\Service\Content\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Content\FileService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class FileServiceTest extends BaseTestCase
{   
    
    public function testGetFile()
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