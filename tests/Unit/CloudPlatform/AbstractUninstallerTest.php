<?php

namespace Tests\Unit\CloudPlatform;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Topxia\Service\Common\ServiceKernel;

class AbstractUninstallerTest extends BaseTestCase
{
    public function testGetConnection()
    {
        $stub = $this->mockAbstractUninstaller();
        $return = $stub->getConnection();
        $this->assertTrue(is_object($return));
    }

    public function testCreateService()
    {
        $stub = $this->mockAbstractUninstaller();
        $return = ReflectionUtils::invokeMethod($stub, 'createService', array('Course:CourseService'));
        $this->assertTrue(is_object($return));
    }

    public function testCreateDao()
    {
        $stub = $this->mockAbstractUninstaller();
        $return = ReflectionUtils::invokeMethod($stub, 'createDao', array('Course:CourseService'));
        $this->assertTrue(is_object($return));
    }

    public function mockAbstractUninstaller()
    {
        $kernel = ServiceKernel::create('dev', false);

        return $this->getMockForAbstractClass('Biz\CloudPlatform\AbstractUninstaller', array($kernel));
    }
}
