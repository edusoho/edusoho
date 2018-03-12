<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Classroom\Service\ClassroomService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\CourseSetService;

class CourseCopyTest extends BaseTestCase
{
    public function testCopyCourse()
    {
        $courseSet = $this->createNewCourseSet();

        $course = $this->defaultCourse('title1', $courseSet);

        $created = $this->getCourseService()->createCourse($course);

        $course['title'] = 'copied from'.$course['title'];
        $course['copyCourseId'] = $created['id'];
        $copied = $this->getCourseService()->copyCourse($course);

        $this->assertEquals($course['title'], $copied['title']);
        $this->assertEquals($created['courseSetId'], $copied['courseSetId']);
        $this->assertEquals($created['expiryMode'], $copied['expiryMode']);
        $this->assertEquals($created['learnMode'], $copied['learnMode']);
    }

    /**
     * @group current
     */
    public function testCopyCourseWithTeachers()
    {
        $courseSet = $this->createNewCourseSet();

        $course = $this->defaultCourse('test course 1', $courseSet);

        $created = $this->getCourseService()->createCourse($course);

        $user = $this->mockTeacherUser('teacher1');
        $this->getMemberService()->setCourseTeachers($created['id'], array(
            array(
                'id' => $user['id'],
                'isVisible' => 1,
            ),
        ));

        $course['title'] = 'copied from'.$course['title'];
        $course['copyCourseId'] = $created['id'];
        $copied = $this->getCourseService()->copyCourse($course);

        $this->assertEquals($course['title'], $copied['title']);
        $this->assertTrue($this->getMemberService()->isCourseTeacher($copied['id'], $user['id']));
    }

    public function testCopyCourseWithTasks()
    {
        $courseSet = $this->createNewCourseSet();

        $course = $this->defaultCourse('test course 1', $courseSet);

        $created = $this->getCourseService()->createCourse($course);

        $this->createTask($created, 'test-task-1');
        $this->createTask($created, 'test-task-2', 'discuss');

        $course['title'] = 'copied from'.$course['title'];
        $course['copyCourseId'] = $created['id'];
        $copied = $this->getCourseService()->copyCourse($course);

        $this->assertEquals($course['title'], $copied['title']);
        $this->assertEquals(2, $copied['taskNum']);
    }

    public function testCopyClassroomCourse()
    {
        $courseSet = $this->createNewCourseSet();

        $course = $this->defaultCourse('test course 1', $courseSet);

        $created = $this->getCourseService()->createCourse($course);

        $user = $this->mockTeacherUser('teacher1');

        $this->getMemberService()->setCourseTeachers($created['id'], array(
            array(
                'id' => $user['id'],
                'isVisible' => 1,
            ),
        ));

        $classroom = $this->createClassroom();

        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($created['id']));

        $this->assertEquals(1, $this->getClassroomService()->countCoursesByClassroomId($classroom['id']));
        $this->assertTrue($this->getClassroomService()->isClassroomTeacher($classroom['id'], $user['id']));
    }

    private function createClassroom()
    {
        return $this->getClassroomService()->addClassroom(array('title' => 'test classroom'));
    }

    private function mockTeacherUser($name)
    {
        $user = array();
        $user['email'] = 'name-'.$name.'@user.com';
        $user['nickname'] = 'name_'.$name;
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER', 'ROLE_TEACHER');

        return $user;
    }

    protected function createNewCourseSet()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        $this->assertNotEmpty($courseSet);

        return $courseSet;
    }

    protected function defaultCourse($title, $courseSet)
    {
        return  array(
            'title' => $title,
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'courseType' => 'normal',
        );
    }

    protected function createTask($course, $title, $type = 'text')
    {
        $fields = array(
            'title' => $title,
            'mediaType' => $type,
            'fromCourseId' => $course['id'],
            'fromCourseSetId' => 1,
            'finishType' => 'time',
            'status' => 'published',
        );

        $lesson = array(
            'courseId' => $fields['fromCourseId'],
            'title' => $fields['title'],
            'type' => 'lesson',
            'status' => 'created',
        );
        $lesson = $this->getCourseService()->createChapter($lesson);

        $fields['categoryId'] = $lesson['id'];

        return $this->getTaskService()->createTask($fields);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
