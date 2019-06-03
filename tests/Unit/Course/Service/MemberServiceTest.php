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
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testBecomeStudentAndCreateOrderWithParamFilterException()
    {
        $this->getMemberService()->becomeStudentAndCreateOrder(1, 1, array());
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testBecomeStudentAndCreateOrderWithNotExistUser()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'tryManageCourse', 'returnValue' => array()),
        ));

        $this->getMemberService()->becomeStudentAndCreateOrder(-1, 1, array('price' => 0.01, 'remark' => 'test'));
    }

    /**
     * @expectedException \Biz\Course\CourseException
     * @expectedExceptionMessage exception.course.not_found
     */
    public function testBecomeStudentAndCreateOrderWithNotExistCourse()
    {
        $user = $this->createNormalUser();
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'tryManageCourse', 'returnValue' => array()),
            array('functionName' => 'getCourse', 'returnValue' => array()),
        ));

        $this->getMemberService()->becomeStudentAndCreateOrder($user['id'], 1, array('price' => 0.01, 'remark' => 'test'));
    }

    /**
     * @expectedException \Biz\Course\MemberException
     * @expectedExceptionMessage exception.course.member.duplicate_member
     */
    public function testBecomeStudentAndCreateOrderIsDuplicateMember()
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
        $this->getMemberDao()->create($member);

        $this->getMemberService()->becomeStudentAndCreateOrder($user['id'], $course['id'], array('price' => 0.01, 'remark' => 'test'));
    }

    public function testBecomeStudentAndCreateOrderByAdminAdded()
    {
        $newUser = $this->createNormalUser();
        $newCourse = $this->mockNewCourse();
        $this->getCourseService()->publishCourse($newCourse['id']);
        $this->mockNewCourseSet();

        list($course, $member, $order) = $this->getMemberService()->becomeStudentAndCreateOrder($newUser['id'], $newCourse['id'], array('price' => 0, 'remark' => 'test', 'isAdminAdded' => 1));
        $this->assertNotEmpty($member);
        $this->assertEquals(0, $order['id']);
    }

    public function testStickMyCourseByCourseSetId()
    {
        $courseSet = $this->mockNewCourseSet();
        $member = array(
            'courseId' => 3,
            'userId' => 1,
            'courseSetId' => $courseSet['id'],
            'joinedType' => 'course',
            'role' => 'teacher',
        );

        $member = $this->getMemberDao()->create($member);
        $oldStickyTime = $member['stickyTime'];

        $this->getMemberService()->stickMyCourseByCourseSetId($courseSet['id']);
        $member = $this->getMemberService()->getCourseMember(3, 1);

        $this->assertNotEquals($oldStickyTime, $member['stickyTime']);
    }

    public function testUnStickMyCourseByCourseSetId()
    {
        $courseSet = $this->mockNewCourseSet();
        $member = array(
            'courseId' => 3,
            'userId' => 1,
            'courseSetId' => $courseSet['id'],
            'joinedType' => 'course',
            'role' => 'teacher',
            'stickyTime' => time(),
        );

        $member = $this->getMemberDao()->create($member);
        $oldStickyTime = $member['stickyTime'];

        $this->getMemberService()->unStickMyCourseByCourseSetId($courseSet['id']);
        $member = $this->getMemberService()->getCourseMember(3, 1);

        $this->assertNotEquals($oldStickyTime, $member['stickyTime']);
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
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
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
     * @expectedException \Biz\Course\CourseException
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
     * @expectedException \Biz\Course\CourseException
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

    public function testFindMembersByUserIdAndJoinType()
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

        $results = $this->getMemberService()->findMembersByUserIdAndJoinType($user['id']);

        $this->assertCount(1, $results);
    }

    public function testCancelTeacherInAllCourses()
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

        $this->getMemberService()->cancelTeacherInAllCourses($user['id']);
        $result = $this->getMemberService()->getCourseMember($course['id'], $user['id']);

        $this->assertEmpty($result);
    }

    public function testQuitCourseByDeadlineReach()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
            'deadline' => time() - 84600,
        );
        $this->getMemberDao()->create($member);

        $this->getMemberService()->quitCourseByDeadlineReach($user['id'], $course['id']);
        $result = $this->getMemberService()->getCourseMember($course['id'], $user['id']);

        $this->assertEmpty($result);
    }

    /**
     * @expectedException \Biz\Course\CourseException
     * @expectedExceptionMessage exception.course.not_found
     */
    public function testQuitCourseByDeadlineReachWithNotExistCourse()
    {
        $this->mockBiz('Course:CourseService', array(
            array('functionName' => 'getCourse', 'returnValue' => array()),
        ));

        $this->getMemberService()->quitCourseByDeadlineReach(1, -1);
    }

    /**
     * @expectedException \Biz\Course\MemberException
     * @expectedExceptionMessage exception.course.member.not_found
     */
    public function testQuitCourseByDeadlineReachWithNotExistMember()
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

        $this->getMemberService()->quitCourseByDeadlineReach($user['id'], $course['id']);
    }

    /**
     * @expectedException \Biz\Course\MemberException
     * @expectedExceptionMessage exception.course.member.non_expired
     */
    public function testQuitCourseByDeadlineReachWithNonExpired()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
            'deadline' => time() + 84600,
        );
        $this->getMemberDao()->create($member);

        $this->getMemberService()->quitCourseByDeadlineReach($user['id'], $course['id']);
    }

    public function testFindTeacherMembersByUserIdAndCourseSetId()
    {
        $user = $this->createNormalUser();
        $member = array(
            'courseId' => 1,
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'teacher',
            'deadline' => time() + 84600,
        );
        $this->getMemberDao()->create($member);

        $members = $this->getMemberService()->findTeacherMembersByUserIdAndCourseSetId($user['id'], 1);

        $this->assertEquals(1, $members[0]['courseId']);
    }

    public function testCountQuestionsByCourseIdAndUserId()
    {
        $this->mockCourseThread(array('courseId' => 1, 'userId' => 1, 'type' => 'question'));

        $result = $this->getMemberService()->countQuestionsByCourseIdAndUserId(1, 1);
        $this->assertEquals(1, $result);
    }

    public function testCountDiscussionsByCourseIdAndUserId()
    {
        $this->mockCourseThread(array('courseId' => 1, 'userId' => 1, 'type' => 'discussion'));

        $result = $this->getMemberService()->countDiscussionsByCourseIdAndUserId(1, 1);
        $this->assertEquals(1, $result);
    }

    public function testCountActivitiesByCourseIdAndUserId()
    {
        $this->mockTaskResult(array('userId' => 3, 'courseTaskId' => 12, 'time' => 1, 'courseId' => 1));

        $result = $this->getMemberService()->countActivitiesByCourseIdAndUserId(1, 3);
        $this->assertEquals(1, $result);
    }

    public function testCountPostsByCourseIdAndUserId()
    {
        $thread = $this->mockCourseThread(array('courseId' => 1, 'userId' => 1, 'type' => 'discussion'));
        $this->mockCourseThreadPost(array('userId' => 1, 'threadId' => $thread['id']));

        $result = $this->getMemberService()->countPostsByCourseIdAndUserId(1, 1);
        $this->assertEquals(1, $result);
    }

    public function testSearchMemberCountGroupByFields()
    {
        $member = array(
            'courseId' => 1,
            'userId' => 2,
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
            'deadline' => time() + 84600,
        );
        $this->getMemberDao()->create($member);
        $result = $this->getMemberService()->searchMemberCountGroupByFields(array('courseId' => 1), 'courseId', 0, 10);

        $this->assertEquals(1, $result[0]['count']);
    }

    public function testBatchUpdateMemberDeadlinesByDay()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
            'deadline' => time(),
        );
        $this->getMemberDao()->create($member);

        $this->getMemberService()->batchUpdateMemberDeadlinesByDay($course['id'], array(0 => $user['id']), 1, 'minus');
        $result = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        $this->assertEquals($member['deadline'], (int) $result['deadline']);

        $this->getMemberService()->batchUpdateMemberDeadlinesByDay($course['id'], array(0 => $user['id']), 1);
        $result = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        $this->assertEquals($member['deadline'] + 1 * 24 * 60 * 60, $result['deadline']);
    }

    public function testBatchUpdateMemberDeadlinesByDate()
    {
        $user = $this->createNormalUser();
        $course = $this->mockNewCourse();
        $member = array(
            'courseId' => $course['id'],
            'userId' => $user['id'],
            'courseSetId' => 1,
            'joinedType' => 'course',
            'role' => 'student',
            'deadline' => time(),
        );
        $this->getMemberDao()->create($member);

        $this->getMemberService()->batchUpdateMemberDeadlinesByDate($course['id'], array(0 => $user['id']), time() - 86400);
        $result = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        $this->assertEquals($member['deadline'], (int) $result['deadline']);

        $this->getMemberService()->batchUpdateMemberDeadlinesByDate($course['id'], array(0 => $user['id']), time() + 86400);
        $result = $this->getMemberService()->getCourseMember($course['id'], $user['id']);
        $this->assertEquals($member['deadline'] + 24 * 60 * 60, $result['deadline']);
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

    protected function mockCourseThread($fields = array())
    {
        $defaultFields = array(
            'courseId' => 1,
            'taskId' => 1,
            'userId' => 1,
            'type' => 'discussion',
            'title' => 'course thread title',
            'content' => 'course thread content',
            'courseSetId' => 1,
        );

        $fields = array_merge($defaultFields, $fields);

        return $this->getThreadDao()->create($fields);
    }

    protected function mockCourseThreadPost($fields = array())
    {
        $defaultFields = array(
            'courseId' => 1,
            'taskId' => 1,
            'threadId' => 1,
            'userId' => 1,
            'content' => 'post content',
        );

        $fields = array_merge($defaultFields, $fields);

        return $this->getThreadPostDao()->create($fields);
    }

    protected function mockTaskResult($fields = array())
    {
        $taskReult = array_merge($this->getDefaultMockFields(), $fields);
        $this->getTaskResultDao()->create($taskReult);
    }

    protected function getDefaultMockFields()
    {
        return array('activityId' => 1, 'courseTaskId' => 2, 'time' => 1, 'watchTime' => 1);
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

    protected function getThreadDao()
    {
        return $this->createDao('Course:ThreadDao');
    }

    protected function getTaskResultDao()
    {
        return $this->createDao('Task:TaskResultDao');
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Course:ThreadPostDao');
    }
}
