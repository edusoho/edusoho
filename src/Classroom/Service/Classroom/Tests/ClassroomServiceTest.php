<?php

namespace Classroom\Service\Classroom\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\User\CurrentUser;

class ClassroomServiceTest extends BaseTestCase
{
    /*public function testAddClassroom()
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
        $fields = array(
            'title' => 'test11111',
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
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
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
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $enabled = $this->getClassroomService()->canTakeClassroom($classroom['id']);
        $this->assertEquals(true, $enabled);
        
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
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
            'password' => 'adm1in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
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
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER','ROLE_SUPER_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $enabled = $this->getClassroomService()->canLookClassroom($classroom['id']);

        $this->assertEquals(true, $enabled);

        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
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
            'password' => 'adm1in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
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
            'password' => 'adm11in',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
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

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getClassroomService()->publishClassroom($classroom['id']);
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
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

        $course1 = array('title'=>'Test Course 1');
        $course2 = array('title'=>'Test Course 2');
        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getClassroomService()->setClassroomCourses($classroom['id'], array($course1['id'], $course2['id']));

        $enabled = $this->getClassroomService()->isCourseInClassroom(1, $classroom['id']);

        $this->assertEquals(true, $enabled);

        $enabled = $this->getClassroomService()->isCourseInClassroom(4, $classroom['id']);

        $this->assertEquals(false, $enabled);

        $this->getClassroomService()->deleteClassroomCourses($classroom['id'], array(1, 2));

        $enabled = $this->getClassroomService()->isCourseInClassroom(1, $classroom['id']);

        $this->assertEquals(false, $enabled);
    }

    public function testFindClassroomsByCourseId()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );
        $course1 = array('title'=>'Test Course 1');
        $course2 = array('title'=>'Test Course 2');
        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getClassroomService()->setClassroomCourses($classroom['id'], array($course1['id'], $course2['id']));

        $textClassroom = array(
            'title' => 'test1',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getClassroomService()->setClassroomCourses($classroom['id'], array(1, 2));

        $classrooms = $this->getClassroomService()->findClassroomIdsByCourseId(1);

        $this->assertEquals(2, count($classrooms));
    }

    public function testFindCoursesByClassroomId()
    {
        $user = $this->createUser();
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);

        $this->getCourseService()->createCourse(array('title' => 'ssssss'));
        $this->getCourseService()->createCourse(array('title' => 'sss222sss'));

        $this->getClassroomService()->setClassroomCourses($classroom['id'], array(1, 2));

        $courses = $this->getClassroomService()->findCoursesByClassroomId($classroom['id']);

        $this->assertEquals(2, count($courses));
    }*/

    public function testAddHeadTeacher()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $textClassroom = array(
            'title' => 'test',
        );
        $course1 = array('title'=>'Test Course 1');
        $course1 = $this->getCourseService()->createCourse($course1);
        $this->getCourseService()->setCourseTeachers($course1['id'],array(array('id'=>$teacher1['id'],'isVisible' => 1),array('id'=>$teacher2['id'],'isVisible' => 1) ));
    
        $courseIds = array($course1['id']);

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher1['id']);
        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);
        $this->assertEquals($teacher1['id'],$classroom['headTeacherId']);
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher2['id']);
        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);
        $this->assertEquals($teacher2['id'],$classroom['headTeacherId']);

    }

    /*public function testUpdateAssistant()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $teacher3 = $this->createTeacher('3');
        $teacher4 = $this->createTeacher('4');
        $teacher5 = $this->createTeacher('5');
        $teacher6 = $this->createTeacher('6');
        $teacher7 = $this->createTeacher('7');
        $teacher8 = $this->createTeacher('8');
        $textClassroom = array(
            'title' => 'test',
        );

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher2['id']);
        $teacherIds = array($teacher1['id'], $teacher2['id'], $teacher3['id'], $teacher4['id']);
        $this->getClassroomService()->updateAssistants($classroom['id'], $teacherIds);
        $assitantIds = $this->getClassroomService()->findAssistants($classroom['id']);
        $this->assertEquals(count($assitantIds),4);
        $teacherIds = array($teacher1['id'],$teacher3['id'],$teacher5['id'],$teacher7['id']);
        $this->getClassroomService()->updateAssistants($classroom['id'], $teacherIds);
        $assitantIds = $this->getClassroomService()->findAssistants($classroom['id']);
        $this->assertEquals(count($assitantIds),4);
    }

    public function testAddCoursesToClassroom()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $teacher3 = $this->createTeacher('3');
        $teacher4 = $this->createTeacher('4');
        $teacher5 = $this->createTeacher('5');
        $teacher6 = $this->createTeacher('6');
        $textClassroom = array(
            'title' => 'test',
        );
        $course1 = array('title'=>'Test Course 1');
        $course2 = array('title'=>'Test Course 2');
        $course3 = array('title'=>'Test Course 3');

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $course3 = $this->getCourseService()->createCourse($course3);

        $this->getCourseService()->setCourseTeachers($course1['id'],array(array('id'=>$teacher1['id'],'isVisible' => 1),array('id'=>$teacher2['id'],'isVisible' => 1),array('id'=>$teacher3['id'],'isVisible' => 1)));
        $this->getCourseService()->setCourseTeachers($course2['id'],array(array('id'=>$teacher4['id'],'isVisible' => 1),array('id'=>$teacher5['id'],'isVisible' => 1)));
        $this->getCourseService()->setCourseTeachers($course3['id'],array(array('id'=>$teacher1['id'],'isVisible' => 1),array('id'=>$teacher3['id'],'isVisible' => 1),array('id'=>$teacher6['id'],'isVisible' => 1)));
        $this->getClassroomService()->addHeadTeacher($classroom['id'], $teacher2['id']);

        $courseIds = array($course1['id'], $course2['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $teachers = $this->getClassroomService()->findTeachers($classroom['id']);
        $this->assertEquals(count($teachers),5);
        $courseIds = array($course3['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $teachers = $this->getClassroomService()->findTeachers($classroom['id']);
        $this->assertEquals(count($teachers),6);

    }

    public function testUpdateClassroomCourses()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $teacher3 = $this->createTeacher('3');
        $teacher4 = $this->createTeacher('4');
        $teacher5 = $this->createTeacher('5');
        $teacher6 = $this->createTeacher('6');
        $textClassroom = array(
            'title' => 'test',
        );
        $course1 = array('title'=>'Test Course 1');
        $course2 = array('title'=>'Test Course 2');
        $course3 = array('title'=>'Test Course 3');

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $course3 = $this->getCourseService()->createCourse($course3);

        $this->getCourseService()->setCourseTeachers($course1['id'],array(array('id'=>$teacher1['id'],'isVisible' => 1),array('id'=>$teacher2['id'],'isVisible' => 1),array('id'=>$teacher3['id'],'isVisible' => 1)));
        $this->getCourseService()->setCourseTeachers($course2['id'],array(array('id'=>$teacher4['id'],'isVisible' => 1),array('id'=>$teacher5['id'],'isVisible' => 1)));
        $this->getCourseService()->setCourseTeachers($course3['id'],array(array('id'=>$teacher1['id'],'isVisible' => 1),array('id'=>$teacher3['id'],'isVisible' => 1),array('id'=>$teacher6['id'],'isVisible' => 1)));

        $user = $this->getCurrentUser();
        $this->getUserService()->changeUserRoles($user['id'],array('ROLE_USER', 'ROLE_TEACHER', 'ROLE_SUPER_ADMIN'));
        $courseIds = array($course1['id'], $course2['id'], $course3['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $teachers = $this->getClassroomService()->findTeachers($classroom['id']);
        $this->assertEquals(count($teachers),6);
        $courseIds = array('6');
        $this->getClassroomService()->updateClassroomCourses($classroom['id'], $courseIds);
        $teachers = $this->getClassroomService()->findTeachers($classroom['id']);
        $this->assertEquals(count($teachers),5);
    }

    public function testBecomeStudent()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $textClassroom = array(
            'title' => 'test',
        );
        $course1 = array('title'=>'Test Course 1');

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $course1 = $this->getCourseService()->createCourse($course1);
        $this->getCourseService()->setCourseTeachers($course1['id'],array(array('id'=>$teacher2['id'],'isVisible' => 1)));
        $courseIds = array($course1['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $member1 = $this->getClassroomService()->becomeStudent($classroom['id'],$teacher1['id']);
        $member2 = $this->getClassroomService()->becomeStudent($classroom['id'],$teacher2['id']);
        $this->assertEquals($member1['userId'], $teacher1['id']);
        $courseCount = $this->getCourseService()->getCourseStudentCount('2');
        $this->assertEquals($courseCount, 1);
    }

    public function testRemoveStudent()
    {
        $teacher1 = $this->createTeacher('1');
        $teacher2 = $this->createTeacher('2');
        $textClassroom = array(
            'title' => 'test',
        );
        $course1 = array('title'=>'Test Course 1');

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $course1 = $this->getCourseService()->createCourse($course1);
        $this->getCourseService()->setCourseTeachers($course1['id'],array(array('id'=>$teacher1['id'],'isVisible' => 1),array('id'=>$teacher2['id'],'isVisible' => 1)));
        $courseIds = array($course1['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], $courseIds);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $member1 = $this->getClassroomService()->becomeStudent($classroom['id'],$teacher1['id']);
        $member2 = $this->getClassroomService()->becomeStudent($classroom['id'],$teacher2['id']);
        $studentCount1 = $this->getClassroomService()->getClassroomStudentCount($classroom['id']);
        $this->assertEquals($studentCount1,2);
        $this->getClassroomService()->removeStudent($classroom['id'],$member1['userId']);
        $studentCount2 = $this->getClassroomService()->getClassroomStudentCount($classroom['id']);
        $this->assertEquals($studentCount2,1);
    }*/

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

    private function createUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');

        return $this->getUserService()->register($user);
    }

    private function createStudent()
    {
        $user = array();
        $user['email'] = "user@user1.com";
        $user['nickname'] = "use1r";
        $user['password'] = "user1";
        $user['roles'] = array('ROLE_USER');

        return $this->getUserService()->register($user);
    }

    private function createTeacher($id)
    {
        $user = array();
        $user['nickname'] = "user".$id;
        $user['email'] = $user['nickname']."@user.com";
        $user['password'] = "user";
        $user['roles'] = array('ROLE_USER','ROLE_TEACHER');

        return $this->getUserService()->register($user);
    }
}
