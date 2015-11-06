<?php 
namespace Mooc\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\CurrentUser;

class CourseScoreServiceTest extends BaseTestCase
{

	/**
	 * CourseScoreBatchAPITest
	 */
	public function testImportUserCourseScore()
	{
         
        list($user,$createCourse) = $this->publishCourse();
        
        list($normalUser,$score) = $this->addScore($createCourse);


        $userScore = $this->getCourseScoreService()->getUserScoreByUserIdAndCourseId($normalUser['id'],$createCourse['id']);
        
        $this->assertEquals($score['importOtherScore'],$userScore['importOtherScore']);
	}

    public function testUpdateUserCourseScore()
    {
        list($user,$createCourse) = $this->publishCourse();
        
        list($normalUser,$score) = $this->addScore($createCourse);

        $userScore = $this->getCourseScoreService()->getUserScoreByUserIdAndCourseId($normalUser['id'],$createCourse['id']);     

        $updateScore = array('importOtherScore' => '60.0');
        $updateScore = $this->getCourseScoreService()->updateUserCourseScore($userScore['id'],$updateScore);
        
        $this->assertEquals($userScore['id'], $updateScore['id']);
        $this->assertEquals(60.0, $updateScore['importOtherScore']);
    }
    
    public function testSearchMemberScoreCount()
    {
        list($user,$createCourse) = $this->publishCourse();
        
        list($normalUser,$score) = $this->addScore($createCourse);

        $conditions = array('courseId'=>$createCourse['id']);

        $count = $this->getCourseScoreService()->searchMemberScoreCount($conditions);
        $this->assertEquals(1, $count);
    }

    public function testSearchMemberScore()
    {
        list($user,$createCourse) = $this->publishCourse();
        
        list($normalUser,$score) = $this->addScore($createCourse);

        $conditions = array('courseId'=>$createCourse['id']);

        $result = $this->getCourseScoreService()->searchMemberScore($conditions,array('createdTime','desc'),0,1);
        $this->assertCount(1,$result);
    }

    public function testFindUsersByOrganizationId()
    {
        list($user,$createCourse) = $this->publishCourse();
        
        
        list($normalUser,$score) = $this->addScore($createCourse);
        $organizationId = '1';

        $fields = array(
            'organizationId'=> $organizationId,
            'about'=>'test'
            );
        $user = $this->getUserService()->updateUserProfile($normalUser['id'],$fields);
        $result = $this->getCourseScoreService()->findUsersByOrganizationId($organizationId);
        $this->assertEquals($result[0]['userId'], $user['id']);
    }

	public function testAddScoreSetting()
    {
	        $createCourse =  $this->mookCourse();
	        $scoreSettingdata = array(
	        	'credit' => 65,
	        	'courseId' => $createCourse['id'],
                'examWeight' => 60,
                'homeworkWeight' => 20,
                'otherWeight'=> 10,
                'standardScore' => 60,
	        );
	        $scoreSetting = $this->getCourseScoreService()->addScoreSetting($scoreSettingdata);

	       $this->assertEquals($createCourse['id'], $scoreSetting['courseId']);
        	$this->assertEquals($scoreSettingdata['credit'], $scoreSettingdata['credit']);
		//TODOLIST
	}

	public function testUpdateScoreSetting(){
		    $createCourse =  $this->mookCourse();
            $scoreSettingdata = array(
                'credit' => 65,
                'courseId' => $createCourse['id'],
                'examWeight' => 60,
                'homeworkWeight' => 20,
                'otherWeight'=> 10,
                'standardScore' => 60,
            );
	        $scoreSetting = $this->getCourseScoreService()->addScoreSetting($scoreSettingdata);
	        $scoreSetting['credit'] = 85;
	        $updateScore = $this->getCourseScoreService()->updateScoreSetting( $scoreSetting['courseId'], $scoreSetting);
	        $this->assertEquals($createCourse['id'], $updateScore['courseId']);
        	$this->assertEquals(85, $updateScore['credit']);
	}


	public function testGetScoreSettingByCourseId(){
		   $createCourse =  $this->mookCourse();
            $scoreSettingdata = array(
                'credit' => 65,
                'courseId' => $createCourse['id'],
                'examWeight' => 60,
                'homeworkWeight' => 20,
                'otherWeight'=> 10,
                'standardScore' => 60,
            );
	        $scoreSetting = $this->getCourseScoreService()->addScoreSetting($scoreSettingdata);
	        $scoreSetting = $this->getCourseScoreService()->getScoreSettingByCourseId($createCourse['id']);
	        $this->assertEquals($createCourse['id'],   $scoreSetting['courseId']);
	}
    
    private function publishCourse()
    {
        $user = $this->createStudentUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'online test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        return array($user,$createCourse);
    }

    private function addScore($createCourse)
    {
        $normalUser = $this->createNormalUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $joinCourse = $this->getCourseService()->becomeStudent($createCourse['id'],$normalUser['id']);
        $userProfile  = $this->getUserService()->getUserProfile($normalUser['id']);
        $excelData = array(
           'truename' => $userProfile['truename'],
           'staffNo' => $normalUser['staffNo'],
           'importOtherScore' => '90.0'
            );
        $field = array(
            'userId' => $normalUser['id'],
            'courseId' => $createCourse['id']
            );

        $score = array_merge($excelData,$field);

        $this->getCourseScoreService()->addUserCourseScore($score);

        return array($normalUser,$score);
    }

	private function  mookCourse(){
		  $course = array(
	            'title' => 'online test course 1'
	        );
	      return  $this->getCourseService()->createCourse($course);
	}

    private function createStudentUser()
    {
        $user = array();
        $user['email'] = "userStudent@userStudent.com";
        $user['nickname'] = "userStudent";
        $user['password'] = "userStudent";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');
        return $user;

    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = "normal@user.com";
        $user['nickname'] = "normal";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');
        return $user;
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('Mooc:User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getOrganizationService()
    {
        return $this->getServiceKernel()->createService('Mooc:Organization.OrganizationService');
    }

    protected function getCourseScoreService()
    {
        return $this->getServiceKernel()->createService('Mooc:Course.CourseScoreService');
    }
}