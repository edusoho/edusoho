<?php
namespace Topxia\Service\Activity\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Activity\ActivityService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;

class CourseServiceTest extends BaseTestCase
{

	public function testgetActivity(){
		$activity = $this->getActivityService()->getActivity('1');
        var_dump($activity);
	}

     public function testcreateActivity(){
         $activity['title']="PhpAddTest1112";
         $name=$this->getActivityService()->createActivity($activity);
         var_dump($name);
     }

    

    

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity.ActivityService');
    }

}