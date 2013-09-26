<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\ServiceException;
use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;
use Topxia\Common\ArrayToolkit;

class AnnouncementServiceTest extends BaseTestCase
{

    public function testCreateAnnouncement()
	{
		$courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createdAnnouncement = $this->getCourseService()->createAnnouncement($createdCourse['id'], array(
        	'content'=>'create_content'));

        $this->assertNotNull($createdAnnouncement);
   	}

	public function testGetAnnouncement()
	{
		$courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createdAnnouncement = $this->getCourseService()->createAnnouncement($createdCourse['id'], array(
        	'content'=>'create_content'));
        $getedAnnouncement = $this->getCourseService()->getCourseAnnouncement($createdCourse['id'], $createdAnnouncement['id']);
        $this->assertEquals($registeredUser['id'], $getedAnnouncement['userId']);
        $this->assertEquals($createdCourse['id'], $getedAnnouncement['courseId']);
        $this->assertEquals('create_content', $getedAnnouncement['content']);
	}

	public function testFindAnnouncements()
	{
		$courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $announcement1 = $this->getCourseService()->createAnnouncement($createdCourse['id'], array(
        	'content'=>'create_content1'));
        $announcement2 = $this->getCourseService()->createAnnouncement($createdCourse['id'], array(
        	'content'=>'create_content2'));
		$resultAnnouncements = $this->getCourseService()->findAnnouncements($createdCourse['id'], 0, 30);

		$this->assertContains($announcement1, $resultAnnouncements);
		$this->assertContains($announcement2, $resultAnnouncements);
	}

	public function testDeleteAnnouncement()
	{
		$courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createdAnnouncement = $this->getCourseService()->createAnnouncement($createdCourse['id'], array(
        	'content'=>'create_content'));
        $this->getCourseService()->deleteCourseAnnouncement($createdCourse['id'], $createdAnnouncement['id']);
		$getAnnouncement = $this->getCourseService()->getCourseAnnouncement($createdCourse['id'], $createdAnnouncement['id']);
		
        $this->assertNull($getAnnouncement);
	}

    public function testUpdateAnnouncement()
    {
        $courseInfo = array(
            'type' => 'online',
            'title' => 'online test course 1',
        );
        $createdCourse = $this->getCourseService()->createCourse($courseInfo);
        $userInfo = array(
            'nickname'=>'test_nickname', 
            'password'=> 'test_password',
            'email'=>'test_email@email.com'
        );
        $registeredUser = $this->getUserService()->register($userInfo);
        $createdAnnouncement = $this->getCourseService()->createAnnouncement($createdCourse['id'], array(
            'content'=>'create_content'));
        $updateInfo = array('content'=>'update_content');
        $this->getCourseService()->updateAnnouncement($createdCourse['id'], $createdAnnouncement['id'], $updateInfo);
        
        $getAnnouncement = $this->getCourseService()->getCourseAnnouncement($createdCourse['id'], $createdAnnouncement['id']);
        
        $this->assertEquals($updateInfo['content'], $getAnnouncement['content']);
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}