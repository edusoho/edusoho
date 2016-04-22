<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;

class CourseMemberServiceTest extends BaseTestCase
{

	public function testBecomeStudentAndCreateOrder()
	{
		$this->adminLogin();
        $course1 = $this->getCourseService()->createCourse(array('title' => 'test course 1'));

        $this->getCourseService()->publishCourse($course1['id']);

        $student       = $this->createNormalUser();

       	$this->getCourseMemberService()->becomeStudentAndCreateOrder($student['id'], $course1['id'], array('price'=>10,'remark'=>'dsdd'));

       	

	}

	protected function adminLogin()
    {
        $user1       = $this->createAdminUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user1);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        return $currentUser;
    }

    protected function normalLogin()
    {
        $user1       = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user1);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        return $currentUser;
    }

	private function createAdminUser()
    {
        $user              = array();
        $user['email']     = "adminUser@user.com";
        $user['nickname']  = "adminUser";
        $user['password']  = "adminUser";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_TEACHER','ROLE_SUPER_ADMIN');
        return $user;
    }

    private function createNormalUser()
    {
        $user              = array();
        $user['email']     = "normal@user.com";
        $user['nickname']  = "normal";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER');
        return $user;
    }

	protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course.CourseMemberService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}
