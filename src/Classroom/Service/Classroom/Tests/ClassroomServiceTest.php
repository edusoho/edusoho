<?php

namespace Classroom\Service\Classroom\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;

class ClassroomServiceTest extends BaseTestCase
{   
    public function testAddClassroom()
    {   
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->assertEquals(1, $classroom['id']);

        $this->assertEquals($textClassroom['title'], $classroom['title']);

        $this->assertEquals('draft', $classroom['status']);

    }

    public function testUpdateClassroom()
    {   
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $fields=array(
            'title'=>'test11111',
        );

        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $fields);

        $this->assertEquals($fields['title'], $classroom['title']);

        $classroom = $this->getClassroomService()->updateClassroom("999", $fields);

        $this->assertEquals(null, $classroom);


    }

    public function testFindClassroomsByIds()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $classrooms = $this->getClassroomService()->findClassroomsByIds(array(1));

        $this->assertEquals($classroom, $classrooms[1]);
    }

    public function testCanManageClassroom()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password'=>'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);

        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->addHeadTeacher($classroom['id'], 2);

        $enabled = $this->getClassroomService()->canManageClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

    }

    public function testCanTakeClassroom()
    {
        $user = $this->createUser();
        $user = $this->createStudent();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password'=>'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);

        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->addHeadTeacher($classroom['id'], 2);

        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->publishClassroom($classroom['id']);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin@adm1in.com',
            'password'=>'adm1in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $this->getClassroomService()->becomeAuditor($classroom['id'], 3);

        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);

        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->becomeStudent($classroom['id'], 3);
        
        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);
    }

    public function testCanLookClassroom()
    {
        $user = $this->createUser();
        $user = $this->createStudent();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password'=>'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->addHeadTeacher($classroom['id'], 2);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->publishClassroom($classroom['id']);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin@adm1in.com',
            'password'=>'adm1in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $this->getClassroomService()->becomeAuditor($classroom['id'], 3);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->becomeStudent($classroom['id'], 3);
        
        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 4,
            'nickname' => 'admin11',
            'email' => 'admin@adm11in.com',
            'password'=>'adm11in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(false, $enabled);
    }

    public function testExitClassroom()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password'=>'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER')
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser); 

        $this->getClassroomService()->becomeStudent($classroom['id'], 2);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $this->getClassroomService()->exitClassroom($classroom['id'], 2);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(false, $enabled);
    }

    public function testSetClassroomCourses()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getClassroomService()->setClassroomCourses($classroom['id'], array(1,2));

        $enabled = $this->getClassroomService()->isCourseInClassroom(1, $classroom['id']);

        $this->assertEquals(true, $enabled);

        $enabled = $this->getClassroomService()->isCourseInClassroom(4, $classroom['id']);

        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->deleteClassroomCourses($classroom['id'], array(1,2));

        $enabled = $this->getClassroomService()->isCourseInClassroom(1, $classroom['id']);

        $this->assertEquals(false, $enabled);
    }

    public function testFindClassroomsByCourseId()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getClassroomService()->setClassroomCourses($classroom['id'], array(1,2));

        $textClassroom = array(
            'title' => 'test1',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getClassroomService()->setClassroomCourses($classroom['id'], array(1,2));

        $classrooms = $this->getClassroomService()->findClassroomsByCourseId(1);
        
        $this->assertEquals(2, count($classrooms));
    }

    public function testFindCoursesByClassroomId()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getCourseService()->createCourse(array('title'=>'ssssss'));
        $this->getCourseService()->createCourse(array('title'=>'sss222sss'));

        $this->getClassroomService()->setClassroomCourses($classroom['id'], array(1,2));

        $courses=$this->getClassroomService()->findCoursesByClassroomId($classroom['id']);
        
        $this->assertEquals(2, count($courses));
    }

    private function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    private function createUser(){

        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password']= "user";
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        
        return $this->getUserService()->register($user);
    }

    private function createStudent(){

        $user = array();
        $user['email'] = "user@user1.com";
        $user['nickname'] = "use1r";
        $user['password']= "user1";
        $user['roles'] = array('ROLE_USER');
        
        return $this->getUserService()->register($user);
    }

}