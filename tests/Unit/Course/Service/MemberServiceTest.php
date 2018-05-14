<?php

namespace Tests\Unit\Course\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Course\Dao\CourseMemberDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\User\Service\UserService;

class MemberServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage parameter is invalid!
     */
    public function testBecomeStudentAndCreateOrderWithParamFilterException()
    {
        $this->getMemberService()->becomeStudentAndCreateOrder(1, 1, array());
    }

    public function testFindWillOverdueCourses()
    {
        $user = $this->getCurrentUser();
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'findCoursesByIds',
                    'withParams' => array(array(1)),
                    'returnValue' => array(
                        array('id' => 1),
                    ),
                ),
            )
        );
        $member = array(
            'courseId' => 1,
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'deadline' => time() + 3600,
        );
        $member = $this->getMemberDao()->create($member);

        $results = $this->getMemberService()->findWillOverdueCourses();

        $this->assertEquals(array('id' => 1), reset($results[0]));
        $this->assertEquals($member, reset($results[1]));
    }

    public function testFindLatestStudentsByCourseSetId()
    {
        $user = $this->getCurrentUser();
        $member = array(
            'courseId' => 1,
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'deadline' => time() + 3600,
        );
        $member = $this->getMemberDao()->create($member);

        $results = $this->getMemberService()->findLatestStudentsByCourseSetId(1, 0, 10);
        $this->assertCount(1, $results);
        $this->assertEquals($member, reset($results));
    }

    public function testRemoveStudent()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'deadline' => time() + 3600,
        );
        $member = $this->getMemberDao()->create($member);
        $this->assertNotEmpty($member);
        $this->getMemberService()->removeStudent($course['id'], $user['id']);
        $result = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        $this->assertNull($result);
    }

    public function testWaveMember()
    {
        $user = $this->createNormalUser();
        $member = array(
            'courseId' => 1,
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
        );
        $member = $this->getMemberDao()->create($member);

        $this->getMemberService()->waveMember($member['id'], array('learnedNum' => 2));
        $waveMember = $this->getMemberService()->getCourseMember(1, $user['id']);

        $this->assertEquals(2, $waveMember['learnedNum']);
    }

    public function testIsMemberNonExpired()
    {
        $this->mockBiz(
            'CloudPlatform:AppService',
            array(
                array(
                    'functionName' => 'getAppByCode',
                    'withParams' => array('vip'),
                    'returnValue' => false,
                ),
            )
        );

        $course = array(
            'id' => 1,
        );

        $member = array(
            'id' => 1,
            'levelId' => 2,
            'deadline' => 0,
        );

        $result = $this->getMemberService()->isMemberNonExpired($course, $member);

        $this->assertFalse($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage course, member参数不能为空
     */
    public function testIsMemberNonExpiredWithException()
    {
        $this->getMemberService()->isMemberNonExpired(array(), array());
    }

    public function testIsCourseMember()
    {
        $user = $this->createNormalUser();
        $member = array(
            'courseId' => 1,
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
        );
        $this->getMemberDao()->create($member);

        $result = $this->getMemberService()->isCourseMember(1, $user['id']);
        $this->assertTrue($result);
    }

    public function testRemarkStudent()
    {
        $user = $this->createNormalUser();
        $member = array(
            'courseId' => 1,
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
        );
        $this->getMemberDao()->create($member);

        $result = $this->getMemberService()->remarkStudent(1, $user['id'], 'add student');

        $this->assertEquals('add student', $result['remark']);
    }

    public function testGetMemberDeadline()
    {
        $course = $this->mockNewCourse(array('expiryMode' => 'days', 'expiryDays' => 10, 'courseType' => 'default'));

        $result = ReflectionUtils::invokeMethod($this->getMemberService(), 'getMemberDeadline', array($course));

        $this->assertNotEmpty($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testLockStudentWithNotFoundException()
    {
        $user = $this->createNormalUser();
        $member = array(
            'courseId' => 1,
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
        );
        $this->getMemberDao()->create($member);

        $this->getMemberService()->lockStudent(1, $user['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testUnlockStudentWithNotFoundException()
    {
        $user = $this->createNormalUser();
        $member = array(
            'courseId' => 1,
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
        );
        $this->getMemberDao()->create($member);

        $this->getMemberService()->unlockStudent(1, $user['id']);
    }

    public function testLockStudent()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
        );
        $this->getMemberDao()->create($member);

        $this->getMemberService()->lockStudent(1, $user['id']);

        $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);

        $this->assertEquals(1, $member['locked']);
    }

    public function testUnlockStudent()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
        );
        $this->getMemberDao()->create($member);
        $this->getMemberService()->lockStudent(1, $user['id']);

        $this->getMemberService()->unlockStudent(1, $user['id']);

        $member = $this->getMemberService()->getCourseMember($course['id'], $user['id']);

        $this->assertEquals(0, $member['locked']);
    }

    public function testIsCourseTeacher()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'teacher',
        );
        $this->getMemberDao()->create($member);

        $result = $this->getMemberService()->isCourseTeacher($course['id'], $user['id']);

        $this->assertTrue($result);
    }

    public function testIsCourseStudent()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
        );
        $this->getMemberDao()->create($member);
        $result = $this->getMemberService()->isCourseStudent($course['id'], $user['id']);

        $this->assertTrue($result);
    }

    public function testGetCourseStudentCount()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
        );
        $this->getMemberDao()->create($member);

        $result = $this->getMemberService()->getCourseStudentCount($course['id']);
        $this->assertEquals(1, $result);
    }

    public function testFindCourseStudentsByCourseIds()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
        );
        $this->getMemberDao()->create($member);

        $results = $this->getMemberService()->findCourseStudentsByCourseIds(array($course['id']));

        $this->assertCount(2, $results);
    }

    public function testRemoveCourseStudent()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
        );
        $this->getMemberDao()->create($member);

        $result = $this->getMemberService()->removeCourseStudent($course['id'], $user['id']);
        $this->assertEquals(1, $result);
    }

    public function testPrepareConditions()
    {
        $user = $this->createNormalUser();
        $conditions = array(
            'date' => 'yesterday',
            'creator' => $user['nickname'],
            'nickname' => $user['nickname'],
        );

        $result = ReflectionUtils::invokeMethod($this->getMemberService(), 'prepareConditions', array($conditions));

        $this->assertNotEmpty($result['startTimeGreaterThan']);
        $this->assertNotEmpty($result['startTimeLessThan']);
        $this->assertEquals($user['id'], $result['userId']);
    }

    public function testBatchBecomeStudents()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $this->getCourseService()->publishCourse($course['id']);

        $results = $this->getMemberService()->batchBecomeStudents($course['id'], array($user['id']));

        // teacher 1 student 1
        $this->assertCount(2, $results);
    }

    protected function mockNewCourseSet($fields = array())
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSetFields = array_merge($courseSetFields, $fields);
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        return $courseSet;
    }

    protected function mockNewCourse($fields = array())
    {
        $course = array(
            'title' => 'test Course',
            'courseSetId' => 1,
            'learnMode' => 'lockMode',
            'expiryDays' => 0,
            'expiryMode' => 'forever',
            'courseType' => 'default',
        );

        $course = array_merge($course, $fields);

        return $this->getCourseService()->createCourse($course);
    }

    protected function createNewCourse($courseSetId)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array($courseSetId));

        if (empty($courses)) {
            $courseFields = array(
                'title' => '第一个教学计划',
                'courseSetId' => 1,
                'learnMode' => 'lockMode',
                'expiryDays' => 0,
                'expiryMode' => 'forever',
            );

            $course = $this->getCourseService()->createCourse($courseFields);
        } else {
            $course = $courses[0];
        }

        $this->assertNotEmpty($course);

        return $course;
    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = 'normal@user.com';
        $user['nickname'] = 'normal';
        $user['password'] = 'user';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');

        return $user;
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }
}
