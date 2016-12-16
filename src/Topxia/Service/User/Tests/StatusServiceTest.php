<?php
namespace Topxia\Service\User\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;

class StatusServiceTest extends BaseTestCase
{
	public function testSearchStatusesCount()
    {
    	$status = array('courseId'=>1,'type'=>'course','objectType'=>'course','message'=>'sss','properties'=>'sss');
    	$this->getStatusService()->publishStatus($status);
    	$count = $this->getStatusService()->searchStatusesCount(array('courseId'=>1));
    	$this->assertEquals(1,$count);
    }

    protected function getStatusService()
    {
        return ServiceKernel::instance()->getBiz()->service('User:StatusService');
    }
}   