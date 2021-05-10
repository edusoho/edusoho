<?php

namespace Tests\Unit\Classroom\Service;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseTestCase;
use Biz\Classroom\Dao\ClassroomDao;
use Biz\Classroom\Dao\ClassroomMemberDao;
use Biz\Classroom\DateTimeRange;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\MemberService;
use Biz\Classroom\Service\ReportService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\User\Service\UserService;

class ReportServiceTest extends BaseTestCase
{
    public function testGetStudentTrend()
    {
        $classroom = [
            'title' => '班级标题',
            'status' => 'draft',
        ];
        $course = $this->createCourse('计划1', '课程1');

        $user1 = $this->createUser('user1');
        $user2 = $this->createUser('user2');

        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], [$course['id']]);
        $classroom = $this->getClassroomService()->publishClassroom($classroom['id']);

        $member1 = $this->getClassroomService()->becomeStudent($classroom['id'], $user1['id'], []);
        $member2 = $this->getClassroomService()->becomeStudent($classroom['id'], $user2['id'], []);
        $member1JoinTime = strtotime('-1day');
        $member2JoinTime = strtotime('-5days');
        $this->getClassroomMemberDao()->update($member1['id'], ['createdTime' => $member1JoinTime]);
        $this->getClassroomMemberDao()->update($member2['id'], ['createdTime' => $member2JoinTime]);

        $res = ArrayToolkit::index($this->getReportService()->getStudentTrend($classroom['id'], new DateTimeRange(
            date('Y-m-d', strtotime('-2days')),
            date('Y-m-d', strtotime('+1day'))
        )), 'date');

        self::assertEquals(1, $res[date('Y-m-d', $member1JoinTime)]['studentIncrease']);
        self::assertEquals(0, $res[date('Y-m-d', $member2JoinTime)]['studentIncrease']);
    }

    public function testGetStudentDetailList()
    {
        $classroom = [
            'title' => '班级标题',
            'status' => 'draft',
        ];

        $course = $this->createCourse('计划1', '课程1');
        $user1 = $this->createUser('userA');
        $user2 = $this->createUser('userB');
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], [$course['id']]);
        $classroom = $this->getClassroomService()->publishClassroom($classroom['id']);

        $member1 = $this->getClassroomService()->becomeStudent($classroom['id'], $user1['id'], []);
        $member2 = $this->getClassroomService()->becomeStudent($classroom['id'], $user2['id'], []);
        $classroom = $this->getClassroomDao()->update($classroom['id'], [
            'lessonNum' => 12,
            'compulsoryTaskNum' => 10,
            'electiveTaskNum' => 2,
        ]);
        $member1 = $this->getClassroomMemberDao()->update($member1['id'], [
            'learnedNum' => 5,
            'learnedCompulsoryTaskNum' => 4,
            'learnedElectiveTaskNum' => 1,
            'lastLearnTime' => time() - (86400 * 8),
        ]);

        $member2 = $this->getClassroomMemberDao()->update($member2['id'], [
            'learnedNum' => 11,
            'learnedCompulsoryTaskNum' => 10,
            'learnedElectiveTaskNum' => 1,
            'isFinished' => 1,
            'lastLearnTime' => time() - (86400 * 2),
        ]);
        $res0 = ArrayToolkit::index($this->getReportService()->getStudentDetailList($classroom['id'], ['nameOrMobile' => 'user'], 'CompletionRateDesc', 0, 20), 'id');
        self::assertCount(2, $res0);
        self::assertEquals(5, $res0[$member1['id']]['learnedNum']);
        self::assertEquals(11, $res0[$member2['id']]['learnedNum']);

        $res = ArrayToolkit::index($this->getReportService()->getStudentDetailList($classroom['id'], ['nameOrMobile' => 'user', 'filter' => 'unLearnedSevenDays'], 'CompletionRateDesc', 0, 20), 'id');
        self::assertCount(1, $res);
        self::assertEquals(5, $res[$member1['id']]['learnedNum']);

        $res1 = ArrayToolkit::index($this->getReportService()->getStudentDetailList($classroom['id'], ['nameOrMobile' => 'userB', 'filter' => 'unLearnedSevenDays'], 'CompletionRateDesc', 0, 20), 'id');
        self::assertEmpty($res1);

        $res2 = ArrayToolkit::index($this->getReportService()->getStudentDetailList($classroom['id'], ['nameOrMobile' => '18989492142', 'filter' => 'unFinished'], 'CompletionRateAsc', 0, 20), 'id');
        self::assertEmpty($res2);
    }

    public function testGetStudentDetail()
    {
        $classroom = [
            'title' => '班级标题',
            'status' => 'draft',
        ];

        $course = $this->createCourse('计划2', '课程2');
        $user1 = $this->createUser('userA');
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], [$course['id']]);
        $classroom = $this->getClassroomService()->publishClassroom($classroom['id']);

        $member1 = $this->getClassroomService()->becomeStudent($classroom['id'], $user1['id'], []);
        $classroom = $this->getClassroomDao()->update($classroom['id'], [
            'lessonNum' => 12,
            'compulsoryTaskNum' => 10,
            'electiveTaskNum' => 2,
        ]);
        $member1 = $this->getClassroomMemberDao()->update($member1['id'], [
            'learnedNum' => 5,
            'learnedCompulsoryTaskNum' => 4,
            'learnedElectiveTaskNum' => 1,
            'lastLearnTime' => time() - (86400 * 8),
        ]);

        $res0 = $this->getReportService()->getStudentDetail($classroom['id'], $member1['userId']);
        self::assertEquals($member1['id'], $res0['id']);
    }

    public function testGetStudentDetailCount()
    {
        $classroom = [
            'title' => '班级标题',
            'status' => 'draft',
        ];

        $course = $this->createCourse('计划1', '课程1');
        $user1 = $this->createUser('userA');
        $user2 = $this->createUser('userB');
        $user3 = $this->createUser('userC');
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], [$course['id']]);
        $classroom = $this->getClassroomService()->publishClassroom($classroom['id']);

        $member1 = $this->getClassroomService()->becomeStudent($classroom['id'], $user1['id'], []);
        $member2 = $this->getClassroomService()->becomeStudent($classroom['id'], $user2['id'], []);
        $member3 = $this->getClassroomService()->becomeStudent($classroom['id'], $user3['id'], []);
        $classroom = $this->getClassroomDao()->update($classroom['id'], [
            'lessonNum' => 12,
            'compulsoryTaskNum' => 10,
            'electiveTaskNum' => 2,
        ]);
        $this->getClassroomMemberDao()->update($member1['id'], [
            'learnedNum' => 5,
            'learnedCompulsoryTaskNum' => 4,
            'learnedElectiveTaskNum' => 1,
            'lastLearnTime' => time() - (86400 * 8),
        ]);

        $this->getClassroomMemberDao()->update($member2['id'], [
            'learnedNum' => 11,
            'learnedCompulsoryTaskNum' => 10,
            'learnedElectiveTaskNum' => 1,
            'isFinished' => 1,
            'lastLearnTime' => time() - (86400 * 2),
        ]);
        $this->getClassroomMemberDao()->update($member3['id'], [
            'learnedNum' => 11,
            'learnedCompulsoryTaskNum' => 9,
            'learnedElectiveTaskNum' => 2,
            'isFinished' => 1,
            'lastLearnTime' => time() - (86400 * 8),
        ]);
        $count = $this->getReportService()->getStudentDetailCount($classroom['id'], ['filter' => 'unLearnedSevenDays']);
        self::assertEquals(2, $count);
    }

    public function testGetCourseDetailList()
    {
        $classroom = [
        'title' => '班级标题',
        'status' => 'draft',
    ];

        $course = $this->createCourse('计划1', '课程1');
        $user1 = $this->createUser('userA');
        $user2 = $this->createUser('userB');
        $user3 = $this->createUser('userC');
        $classroom = $this->getClassroomService()->addClassroom($classroom);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], [$course['id']]);
        $classroom = $this->getClassroomService()->publishClassroom($classroom['id']);

        $member1 = $this->getClassroomService()->becomeStudent($classroom['id'], $user1['id'], []);
        $member2 = $this->getClassroomService()->becomeStudent($classroom['id'], $user2['id'], []);
        $member3 = $this->getClassroomService()->becomeStudent($classroom['id'], $user3['id'], []);
        $classroom = $this->getClassroomDao()->update($classroom['id'], [
            'lessonNum' => 12,
            'compulsoryTaskNum' => 10,
            'electiveTaskNum' => 2,
        ]);
        $this->getClassroomMemberDao()->update($member1['id'], [
            'learnedNum' => 5,
            'learnedCompulsoryTaskNum' => 4,
            'learnedElectiveTaskNum' => 1,
            'lastLearnTime' => time() - (86400 * 8),
        ]);

        $this->getClassroomMemberDao()->update($member2['id'], [
            'learnedNum' => 11,
            'learnedCompulsoryTaskNum' => 10,
            'learnedElectiveTaskNum' => 1,
            'isFinished' => 1,
            'lastLearnTime' => time() - (86400 * 2),
        ]);
        $this->getClassroomMemberDao()->update($member3['id'], [
            'learnedNum' => 11,
            'learnedCompulsoryTaskNum' => 9,
            'learnedElectiveTaskNum' => 2,
            'isFinished' => 1,
            'lastLearnTime' => time() - (86400 * 8),
        ]);

        $res = $this->getReportService()->getCourseDetailList($classroom['id'], [], 0, 100);
        self::assertCount(1, $res);
        self::assertEquals(0, $res[0]['finishedNum']);
    }

    public function testGetCourseDetailCount()
    {
    }

    public function testPrepareStudentDetailFilterConditions()
    {
    }

    public function testPrepareStudentDetailSort()
    {
    }

    public function testGetPercent()
    {
    }

    private function createCourse($title = '计划标题', $courseSetTitle = '将要复制到班级的课程')
    {
        $courseSet = [
            'title' => $courseSetTitle,
            'type' => 'normal',
        ];

        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);
        $course = $this->mockCourse($title);
        $course['courseSetId'] = $courseSet['id'];

        return $this->getCourseService()->createCourse($course);
    }

    protected function mockCourse($title = 'Test Course 1')
    {
        return [
            'title' => $title,
            'courseSetId' => 1,
            'learnMode' => 'freeMode',
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        ];
    }

    protected function createUser($nickname = 'test', $roles = ['ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'])
    {
        $user = [];
        $user['email'] = "{$nickname}@user.com";
        $user['nickname'] = $nickname;
        $user['password'] = 'user123';
        $user = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = $roles;

        return $user;
    }

    /**
     * @return MemberService
     */
    protected function getClassroomMemberService()
    {
        return $this->biz->service('Classroom:MemberService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return ReportService
     */
    protected function getReportService()
    {
        return $this->biz->service('Classroom:ReportService');
    }

    /**
     * @return ClassroomMemberDao
     */
    protected function getClassroomMemberDao()
    {
        return $this->biz->dao('Classroom:ClassroomMemberDao');
    }

    /**
     * @return ClassroomDao
     */
    protected function getClassroomDao()
    {
        return $this->biz->dao('Classroom:ClassroomDao');
    }
}
