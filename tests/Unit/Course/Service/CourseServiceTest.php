<?php

namespace Tests\Unit\Course\Service;

use Biz\Activity\Dao\ActivityDao;
use Biz\Activity\Service\ActivityService;
use Biz\BaseTestCase;
use Biz\Course\Dao\CourseMemberDao;
use Biz\File\Service\UploadFileService;
use Biz\Task\Strategy\Impl\DefaultStrategy;
use Biz\Task\Strategy\Impl\NormalStrategy;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\CourseSetService;
use Biz\Classroom\Service\ClassroomService;
use AppBundle\Common\ReflectionUtils;
use AppBundle\Common\TimeMachine;

class CourseServiceTest extends BaseTestCase
{
    public function testSortCourse()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('course title 1', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);

        $course = $this->defaultCourse('course title 2', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->sortCourse($courseSet['id'], array(2, 1));
        $courses = $this->getCourseService()->findCoursesByIds(array(1, 2));
        $this->assertEquals(1, $courses[2]['seq']);
        $this->assertEquals(2, $courses[1]['seq']);
    }

    /**
     * @expectedException \Biz\Course\CourseException
     */
    public function testSortCourseAccessDenied()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('course title 1', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);

        $course = $this->defaultCourse('course title 2', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->sortCourse(1, array(2, 1, 4, 5));
    }

    public function testRecommendCourseByCourseSetId()
    {
        TimeMachine::setMockedTime(time());
        $courseParams = $this->defaultCourse('默认教学计划', array('id' => 1));
        $course = $this->getCourseService()->createCourse($courseParams);
        $this->getCourseService()->recommendCourseByCourseSetId(1, array(
            'recommended' => 1,
            'recommendedTime' => TimeMachine::time(),
            'recommendedSeq' => 1,
        ));
        $course = $this->getCourseService()->getCourse($course['id']);
        $this->assertEquals($course['recommended'], 1);
        $this->assertEquals($course['recommendedTime'], TimeMachine::time());
        $this->assertEquals($course['recommendedSeq'], 1);

        try {
            $errorMessage = '';
            $this->getCourseService()->recommendCourseByCourseSetId(1, array(
                'recommended' => 1,
                'recommendedTime' => TimeMachine::time(),
            ));
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
        }
        $this->assertEquals($errorMessage, 'exception.common_parameter_missing');
    }

    public function testCancelRecommendCourseByCourseSetId()
    {
        TimeMachine::setMockedTime(time());
        $courseParams = $this->defaultCourse('默认教学计划', array('id' => 1));
        $course = $this->getCourseService()->createCourse($courseParams);
        $this->getCourseService()->recommendCourseByCourseSetId(1, array(
            'recommended' => 1,
            'recommendedTime' => TimeMachine::time(),
            'recommendedSeq' => 1,
        ));
        $course = $this->getCourseService()->getCourse($course['id']);
        $this->assertEquals($course['recommended'], 1);
        $this->assertEquals($course['recommendedTime'], TimeMachine::time());
        $this->assertEquals($course['recommendedSeq'], 1);

        $this->getCourseService()->cancelRecommendCourseByCourseSetId(1);
        $course = $this->getCourseService()->getCourse($course['id']);
        $this->assertEquals($course['recommended'], 0);
        $this->assertEquals($course['recommendedTime'], 0);
        $this->assertEquals($course['recommendedSeq'], 0);
    }

    public function testUpdateMaxRate()
    {
        $courseParams = $this->defaultCourse('默认教学计划', array('id' => 1));
        $course = $this->getCourseService()->createCourse($courseParams);
        $newCourse = $this->getCourseService()->updateMaxRate($course['id'], 4);
        $this->assertEquals($course['maxRate'], 0);
        $this->assertEquals($newCourse['maxRate'], 4);
    }

    public function testUpdateMaxRateByCourseSetId()
    {
        $courseParams = $this->defaultCourse('默认教学计划', array('id' => 1));
        $course = $this->getCourseService()->createCourse($courseParams);
        $this->getCourseService()->updateMaxRateByCourseSetId($course['id'], 4);
        $newCourse = $this->getCourseService()->getCourse($course['id']);
        $this->assertEquals($course['maxRate'], 0);
        $this->assertEquals($newCourse['maxRate'], 4);
    }

    public function testUpdateCourseRewardPoint()
    {
        $courseParams = $this->defaultCourse('默认教学计划', array('id' => 1));
        $course = $this->getCourseService()->createCourse($courseParams);
        $newCourse = $this->getCourseService()->updateCourseRewardPoint(
            $course['id'],
            array(
                'taskRewardPoint' => 10,
                'rewardPoint' => 5,
                'title' => 'changeTitle',
            )
        );
        $this->assertEquals($course['taskRewardPoint'], 0);
        $this->assertEquals($newCourse['taskRewardPoint'], 10);
        $this->assertEquals($course['rewardPoint'], 0);
        $this->assertEquals($newCourse['rewardPoint'], 5);
        $this->assertEquals($course['title'], '默认教学计划');
        $this->assertEquals($newCourse['title'], '默认教学计划');
    }

    public function testValidateCourseRewardPoint()
    {
        //全为空
        $result = $this->getCourseService()->validateCourseRewardPoint(array());
        $this->assertTrue(!$result);
        //一个通过
        $result = $this->getCourseService()->validateCourseRewardPoint(array('rewardPoint' => 100));
        $this->assertTrue(!$result);
        //一个不通过
        $result = $this->getCourseService()->validateCourseRewardPoint(array('rewardPoint' => 100001));
        $this->assertTrue($result);
        //两个通过
        $result = $this->getCourseService()->validateCourseRewardPoint(array('taskRewardPoint' => 1, 'rewardPoint' => 1000));
        $this->assertTrue(!$result);
        //两个有一个不通过
        $result = $this->getCourseService()->validateCourseRewardPoint(array('taskRewardPoint' => 1, 'rewardPoint' => 100001));
        $this->assertTrue($result);
    }

    public function testValidateExpiryModeWhenIsEmpty()
    {
        $course = array(
            'expiryStartDate' => 1,
            'expiryEndDate' => 2,
            'expiryDays' => 3,
        );
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        $this->assertArrayEquals($course, $result);
    }

    public function testValidateExpiryModeWhenIsDays()
    {
        //happy pass
        $course = array(
            'expiryMode' => 'days',
            'expiryStartDate' => 1,
            'expiryEndDate' => 2,
            'expiryDays' => 3,
        );
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        $this->assertArrayEquals(array(
            'expiryMode' => 'days',
            'expiryStartDate' => null,
            'expiryEndDate' => null,
            'expiryDays' => 3,
        ), $result);

        //error path
        unset($course['expiryDays']);
        try {
            $message = '';
            $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('exception.course.expirydays_required', $message);
    }

    public function testValidateExpiryModeWhenIsEnd_date()
    {
        //happy pass1 str
        $course = array(
            'expiryMode' => 'end_date',
            'expiryStartDate' => 1,
            'expiryEndDate' => '2018-04-20',
            'expiryDays' => 3,
        );
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        $this->assertArrayEquals(array(
            'expiryMode' => 'end_date',
            'expiryStartDate' => null,
            'expiryEndDate' => strtotime($course['expiryEndDate'].' 23:59:59'),
            'expiryDays' => 0,
        ), $result);

        //happy pass2 timestamp
        $course['expiryEndDate'] = strtotime($course['expiryEndDate'].' 23:59:59');
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        $this->assertArrayEquals(array(
            'expiryMode' => 'end_date',
            'expiryStartDate' => null,
            'expiryEndDate' => $course['expiryEndDate'],
            'expiryDays' => 0,
        ), $result);

        //error path
        unset($course['expiryEndDate']);
        try {
            $message = '';
            $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('exception.course.expiryenddate_required', $message);
    }

    public function testValidateExpiryModeWhenIsDate()
    {
        //happy pass1 str
        $course = array(
            'expiryMode' => 'date',
            'expiryStartDate' => '2018-04-10',
            'expiryEndDate' => '2018-04-20',
            'expiryDays' => 3,
        );
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        $this->assertArrayEquals(array(
            'expiryMode' => 'date',
            'expiryStartDate' => strtotime($course['expiryStartDate']),
            'expiryEndDate' => strtotime($course['expiryEndDate'].' 23:59:59'),
            'expiryDays' => 0,
        ), $result);

        //happy pass2 timestamp
        $course['expiryStartDate'] = strtotime($course['expiryStartDate']);
        $course['expiryEndDate'] = strtotime($course['expiryEndDate'].' 23:59:59');
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        $this->assertArrayEquals(array(
            'expiryMode' => 'date',
            'expiryStartDate' => $course['expiryStartDate'],
            'expiryEndDate' => $course['expiryEndDate'],
            'expiryDays' => 0,
        ), $result);

        //error path1 startDate not set
        unset($course['expiryStartDate']);
        try {
            $message = '';
            $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('exception.course.expirystartdate_required', $message);

        //error path2 endDate not set
        $course['expiryStartDate'] = '2018-04-10';
        unset($course['expiryEndDate']);
        try {
            $message = '';
            $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('exception.course.expiryenddate_required', $message);

        //error path3 endDate<=startDate
        $course['expiryStartDate'] = '2018-04-10';
        $course['expiryEndDate'] = '2018-04-09';
        try {
            $message = '';
            $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('exception.course.expirydate_end_later_than_start', $message);
    }

    public function testValidateExpiryModeWhenIsForever()
    {
        $course = array(
            'expiryMode' => 'forever',
            'expiryStartDate' => '2018-04-10',
            'expiryEndDate' => '2018-04-20',
            'expiryDays' => 3,
        );
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        $this->assertArrayEquals(array(
            'expiryMode' => 'forever',
            'expiryStartDate' => 0,
            'expiryEndDate' => 0,
            'expiryDays' => 0,
        ), $result);
    }

    public function testValidateExpiryModeWhenIsOther()
    {
        $course = array(
            'expiryMode' => 'other_mode',
            'expiryStartDate' => '2018-04-10',
            'expiryEndDate' => '2018-04-20',
            'expiryDays' => 3,
        );
        try {
            $message = '';
            $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'validateExpiryMode', array($course));
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        $this->assertEquals('exception.course.expirymode_invalid', $message);
    }

    public function testUpdateMembersDeadlineByClassroomId()
    {
        $textClassroom = array(
            'title' => 'test',
        );
        $courseSet = $this->createNewCourseSet();

        $course = $this->defaultCourse('course title 1', $courseSet);

        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);
        $user = $this->createNormalUser();

        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($createCourse['id']));
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $classroom = $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);

        $this->getClassroomService()->becomeStudent($classroom['id'], $user['id']);

        $updated = $this->getMemberService()->updateMembersDeadlineByClassroomId($classroom['id'], '1488433547');

        $this->assertEquals(1, $updated);
    }

    public function testFindCoursesByCourseSetIds()
    {
        $course = $this->defaultCourse('第一个教学计划', array('id' => 1));

        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);
        $courses = $this->getCourseService()->findCoursesByCourseSetIds(array(1));
        $this->assertEquals(sizeof($courses), 1);
    }

    public function testGetDefaultCoursesByCourseSetIds()
    {
        $course = $this->defaultCourse('默认教学计划', array('id' => 1));
        $course['isDefault'] = '1';
        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);

        $courses = $this->getCourseService()->getDefaultCoursesByCourseSetIds(array(1));
        $this->assertEquals(sizeof($courses), 1);
        $defaultCourse = reset($courses);

        $this->assertEquals($defaultCourse['title'], $course['title']);
    }

    public function testSetDefaultCourse()
    {
        $courseSet = $this->createNewCourseSet();
        $this->assertNotNull($courseSet);

        $newCourse = $this->defaultCourse('course title 1', $courseSet, 0);
        $newCourse = $this->getCourseService()->createCourse($newCourse);
        $this->assertNotNull($newCourse);

        $this->getCourseService()->setDefaultCourse($courseSet['id'], $newCourse['id']);

        $defaultCourse = $this->getCourseService()->getDefaultCourseByCourseSetId($courseSet['id']);
        $this->assertEquals($newCourse['id'], $defaultCourse['id']);
    }

    public function testGetSeqMinPublishedCourseByCourseSetId()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('course title 1', $courseSet, 0);
        $this->getCourseService()->createCourse($course);

        $course = $this->defaultCourse('course title 2', $courseSet, 0);
        $this->getCourseService()->createCourse($course);

        $this->getCourseService()->publishCourse(1);
        $this->getCourseService()->publishCourse(2);
        $this->getCourseService()->publishCourse(3);

        $this->getCourseService()->sortCourse($courseSet['id'], array(3, 2, 1));
        $courses = $this->getCourseService()->findCoursesByIds(array(1, 2, 3));
        $this->assertEquals(1, $courses[3]['seq']);
        $this->assertEquals(2, $courses[2]['seq']);
        $this->assertEquals(3, $courses[1]['seq']);

        $minPublishedCourse = $this->getCourseService()->getSeqMinPublishedCourseByCourseSetId($courseSet['id']);

        $this->assertEquals(3, $minPublishedCourse['id']);
    }

    public function testCreateCourse()
    {
        $course = $this->defaultCourse('默认教学计划', array('id' => 1));
        $result = $this->getCourseService()->createCourse($course);

        $this->assertEquals($result['title'], $course['title']);
        $this->assertEquals($result['courseType'], $course['courseType']);
    }

    public function testUpdateBaseInfo()
    {
        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('course title 1', $courseSet);
        $createCourse = $this->getCourseService()->createCourse($course);

        $fields = array('title' => 'test Title');

        $this->getCourseService()->updateBaseInfo($createCourse['id'], $fields);
        $course = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals('test Title', $course['title']);

        $this->getCourseService()->publishCourse($createCourse['id']);
        $fields = array('services' => array('service'));

        $this->getCourseService()->updateBaseInfo($createCourse['id'], $fields);
        $course = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals(1, $course['showServices']);
    }

    /**
     * @group current
     */
    public function testFindCoursesByCourseSetId()
    {
        $course = $this->defaultCourse('第一个教学计划', array('id' => 1));

        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);
        $courses = $this->getCourseService()->findCoursesByCourseSetId(1);
        $this->assertEquals(sizeof($courses), 1);
    }

    public function testCreateAndGet()
    {
        $course = $this->defaultCourse('第一个教学计划', array('id' => 1));
        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);

        $created = $this->getCourseService()->getCourse($result['id']);
        $this->assertEquals($result['title'], $created['title']);
    }

    public function testUpdate()
    {
        $course = $this->defaultCourse('第一个教学计划', array('id' => 1));
        $result = $this->getCourseService()->createCourse($course);

        $result['title'] = '第一个教学计划(改)';
        unset($result['learnMode']);

        $updated = $this->getCourseService()->updateCourse($result['id'], $result);

        $this->assertEquals($updated['title'], $result['title']);
    }

    public function testUpdateCourseMarketing()
    {
        $this->saveStorage();

        $courseSet = $this->createNewCourseSet();
        $course = $this->defaultCourse('第一个教学计划', $courseSet);

        $result = $this->getCourseService()->createCourse($course);

        $result['isFree'] = 0;
        $result['originPrice'] = 111;
        $result['vipLevelId'] = 1;
        $result['buyable'] = 1;
        $result['tryLookable'] = 1;
        $result['tryLookLength'] = 2;
        $result['watchLimit'] = 3;
        $result['services'] = array('xxx', 'yy', 'zzz');
        $updated = $this->getCourseService()->updateCourseMarketing($result['id'], $result);

        $this->assertEquals($result['originPrice'], $updated['price']);
        $this->assertEquals($result['vipLevelId'], $updated['vipLevelId']);
        $this->assertEquals($result['buyable'], $updated['buyable']);
        $this->assertEquals($result['tryLookable'], $updated['tryLookable']);
        $this->assertEquals($result['tryLookLength'], $updated['tryLookLength']);
        $this->assertEquals($result['watchLimit'], $updated['watchLimit']);
        $this->assertEquals($result['services'], $updated['services']);
    }

    protected function saveStorage()
    {
        $this->getSettingService()->set('storage', array('upload_mode' => 'cloud'));
    }

    public function testDelete()
    {
        $course = $this->defaultCourse('第一个教学计划', array('id' => 1));

        $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->createCourse($course);

        $deleted = $this->getCourseService()->deleteCourse($result['id']);

        $this->assertEquals($deleted, 2);
    }

    public function testCloseCourse()
    {
        $course = $this->defaultCourse('第一个教学计划', array('id' => 1));

        $result = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($result['id']);
        $this->getCourseService()->closeCourse($result['id']);

        $closed = $this->getCourseService()->getCourse($result['id']);

        $this->assertTrue('closed' == $closed['status']);
    }

    public function testPublishCourse()
    {
        $course = $this->defaultCourse('第一个教学计划', array('id' => 1));

        $result = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->publishCourse($result['id']);

        $published = $this->getCourseService()->getCourse($result['id']);
        $this->assertEquals($published['status'], 'published');
    }

    public function testHasNoTitleForDefaultPlanInMulPlansCourse()
    {
        $defaultCourse = $this->createDefaultCourse('', array('id' => 1));
        $hasNoTitle = $this->getCourseService()->hasNoTitleForDefaultPlanInMulPlansCourse($defaultCourse['id']);
        $this->assertFalse($hasNoTitle);

        $secondCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $hasNoTitle = $this->getCourseService()->hasNoTitleForDefaultPlanInMulPlansCourse($defaultCourse['id']);
        $this->assertTrue($hasNoTitle);
    }

    public function testPublishAndSetDefaultCourseType()
    {
        $defaultCourse = $this->createDefaultCourse('', array('id' => 1));
        $secondCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $this->assertEquals('', $defaultCourse['title']);
        $this->assertEquals('draft', $secondCourse['status']);

        $this->getCourseService()->publishAndSetDefaultCourseType($secondCourse['id'], '设置的计划名');

        $updatedDefaultCourse = $this->getCourseService()->getCourse($defaultCourse['id']);
        $updatedSecondCourse = $this->getCourseService()->getCourse($secondCourse['id']);

        $this->assertEquals('设置的计划名', $updatedDefaultCourse['title']);
        $this->assertEquals('published', $updatedSecondCourse['status']);
    }

    public function testHasMulCourses()
    {
        $defaultCourse = $this->createDefaultCourse('', array('id' => 1));
        $secondCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $hasMulCourses = $this->getCourseService()->hasMulCourses($defaultCourse['courseSetId']);
        $this->assertTrue($hasMulCourses);

        $hasMulCourses = $this->getCourseService()->hasMulCourses($defaultCourse['courseSetId'], 1);
        $this->assertFalse($hasMulCourses);
    }

    public function testIsCourseSetCoursesSummaryEmpty()
    {
        $defaultCourse = $this->createDefaultCourse('', array('id' => 1));
        $secondCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $isCoursesSummaryEmpty = $this->getCourseService()->isCourseSetCoursesSummaryEmpty($defaultCourse['courseSetId']);
        $this->assertFalse($isCoursesSummaryEmpty);

        $course = $this->getCourseService()->updateCourse(2, array('summary' => '计划简介'));
        $isCoursesSummaryEmpty = $this->getCourseService()->isCourseSetCoursesSummaryEmpty($defaultCourse['courseSetId']);
        $this->assertTrue($isCoursesSummaryEmpty);
    }

    public function testFindCourseItems()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);
        $this->createActivity(array('title' => 'activity 1'));
        $defaultTask = $this->createTask('default', $defaultCourse['id']);

        $result = $this->getCourseService()->findCourseItems($defaultCourse['id']);
        $this->assertEquals($defaultTask['title'], $result['task-1']['title']);
    }

    public function testFindCourseItemsByPaging()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);

        $result = $this->getCourseService()->findCourseItemsByPaging($defaultCourse['id']);
        $this->assertEmpty($result[1]);
    }

    /**
     * @expectedException \Biz\Course\CourseException
     * @expectedExceptionMessage exception.course.not_found
     */
    public function testFindCourseItemsByPagingWithExistCourse()
    {
        $this->getCourseService()->findCourseItemsByPaging(1);
    }

    public function testFindStudentsByCourseId()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $result = $this->getCourseService()->findStudentsByCourseId($defaultCourse['id']);
        $this->assertEmpty($result);
    }

    public function testFindTeachersByCourseId()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $result = $this->getCourseService()->findTeachersByCourseId($defaultCourse['id']);
        $this->assertEquals($defaultCourse['id'], $result[0]['courseId']);
    }

    public function testCountThreadsByCourseId()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $count = $this->getCourseService()->countThreadsByCourseId($defaultCourse['id']);
        $this->assertEquals(0, $count);
    }

    public function testGetUserRoleInCourse()
    {
        $user = $this->getCurrentUser();
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $result = $this->getCourseService()->getUserRoleInCourse($defaultCourse['id'], $user['id']);
        $this->assertEquals('teacher', $result);
    }

    public function testFindUserTeachingCoursesByCourseSetId()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $result = $this->getCourseService()->findUserTeachingCoursesByCourseSetId($defaultCourse['id']);
        $this->assertEmpty($result);

        $result = $this->getCourseService()->findUserTeachingCoursesByCourseSetId($defaultCourse['id'], false);
        $this->assertEquals('第二个教学计划', $result[1]['title']);
    }

    public function testFindPriceIntervalByCourseSetIds()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $result = $this->getCourseService()->findPriceIntervalByCourseSetIds(array(1));
        $this->assertEquals('0.00', $result[1]['minPrice']);
        $this->assertEquals('0.00', $result[1]['maxPrice']);
    }

    public function testCanJoinCourse()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $result = $this->getCourseService()->canJoinCourse($defaultCourse['id']);
        $this->assertEquals('course.unpublished', $result['code']);
    }

    public function testCanLearnCourse()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $result = $this->getCourseService()->canLearnCourse($defaultCourse['id']);
        $this->assertEquals('course.unpublished', $result['code']);
    }

    public function testCanLearnTask()
    {
        $defaultTask = $this->createTask('default', 1);

        $result = $this->getCourseService()->canLearnTask($defaultTask['id']);
        $this->assertEquals('course.not_found', $result['code']);
    }

    public function testSortCourseItems()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);

        $result = $this->getCourseService()->sortCourseItems($defaultCourse['id'], array());
        $this->assertEmpty($result);
    }

    public function testDeleteChapter()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $mockChapter = $this->createChapter($defaultCourse['id'], 'test Chapter');

        $this->getCourseService()->deleteChapter($defaultCourse['id'], $mockChapter['id']);
        $result = $this->getCourseService()->getChapter($defaultCourse['id'], $mockChapter['id']);
        $this->assertEmpty($result);
    }

    public function testDeleteChapterWithNoChapter()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:CourseChapterDao', array(
            array('functionName' => 'get', 'returnValue' => array()),
        ));

        $result = $this->getCourseService()->deleteChapter($defaultCourse['id'], 1);
        $this->assertEmpty($result);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testDeleteChapterWithErrorParam()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:CourseChapterDao', array(
            array('functionName' => 'get', 'returnValue' => array('courseId' => 10)),
        ));

        $this->getCourseService()->deleteChapter($defaultCourse['id'], 1);
    }

    public function testCountUserLearningCourses()
    {
        $count = $this->getCourseService()->countUserLearningCourses(1);

        $this->assertEquals(0, $count);
    }

    public function testFindUserLearningCourses()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findLearningMembers', 'returnValue' => array(array('courseId' => 1, 'learnedNum' => 1))),
        ));

        $result = $this->getCourseService()->findUserLearningCourses(1, 0, 5);
        $this->assertEquals(1, $result[0]['memberLearnedNum']);
    }

    public function testFindLearnedCoursesByCourseIdAndUserId()
    {
        $course1 = $this->defaultCourse('test course 1', array('id' => 1));

        $course2 = $this->defaultCourse('test course 2', array('id' => 1));
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse2['id']);

        $lesson1 = array(
            'courseId' => $createCourse1['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test lesson',
            'number' => '1',
            'summary' => '',
            'type' => 'text',
            'seq' => '1',
            'parentId' => 1,
            'userId' => 1,
            'createdTime' => time(),
        );
        $lesson2 = array(
            'courseId' => $createCourse2['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test lesson',
            'number' => '1',
            'summary' => '',
            'type' => 'text',
            'seq' => '1',
            'parentId' => 1,
            'userId' => 1,
            'createdTime' => time(),
        );

        $user = $this->createNormalUser();

        $this->getMemberService()->becomeStudentAndCreateOrder(
            $user['id'],
            $createCourse1['id'],
            array('remark' => '1111', 'price' => 0)
        );
        $this->getMemberService()->becomeStudentAndCreateOrder(
            $user['id'],
            $createCourse2['id'],
            array('remark' => '2222', 'price' => 0)
        );

        $this->getCourseService()->tryTakeCourse($createCourse1['id']);
        $this->getCourseService()->tryTakeCourse($createCourse2['id']);

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);

        $result = $this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($createCourse1['id'], $user['id']);
        $this->assertEmpty($result);
    }

    public function testFindUserLearnCourseIds()
    {
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findUserLearnCourseIds', 'returnValue' => array(1 => array('courseId' => 1), 2 => array('courseId' => 2), 3 => array('courseId' => 3))),
        ));

        $result = $this->getCourseService()->findUserLearnCourseIds(1);

        $this->assertEquals(3, count($result));
    }

    public function testCountUserLearnCourseIds()
    {
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'countUserLearnCourses', 'returnValue' => 3),
        ));

        $result = $this->getCourseService()->countUserLearnCourses(1);

        $this->assertEquals(3, $result);
    }

    public function testCountUserLearnedCourses()
    {
        $count = $this->getCourseService()->countUserLearnedCourses(1);

        $this->assertEquals(0, $count);
    }

    public function testFindUserLearnedCourses()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findLearnedMembers', 'returnValue' => array(array('courseId' => 1, 'learnedNum' => 3))),
        ));

        $result = $this->getCourseService()->findUserLearnedCourses(1, 0, 5);
        $this->assertEquals(3, $result[0]['memberLearnedNum']);
    }

    public function testFindUserTeachCourseCount()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findByUserIdAndRole', 'returnValue' => array(array('courseId' => 1))),
        ));

        $count = $this->getCourseService()->findUserTeachCourseCount(array('userId' => 1));
        $this->assertEquals(1, $count);
    }

    public function testFindUserTeachCourses()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findByUserIdAndRole', 'returnValue' => array(array('courseId' => 1))),
        ));

        $result = $this->getCourseService()->findUserTeachCourses(array('userId' => 1), 0, 10);
        $this->assertEquals('第二个教学计划', $result[0]['title']);
    }

    public function testAnalysisCourseDataByTime()
    {
        $result = $this->getCourseService()->analysisCourseDataByTime(time(), time() + 86400);
        $this->assertEmpty($result);
    }

    public function testFindUserManageCoursesByCourseSetId()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $result = $this->getCourseService()->findUserManageCoursesByCourseSetId(1, 1);
        $this->assertEquals('第二个教学计划', $result[1]['title']);
    }

    public function testFillMembersWithUserInfo()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'fillMembersWithUserInfo', array(array(0 => array('userId' => 1))));
        $this->assertEquals('admin', $result[0]['nickname']);
    }

    public function testPrepareCourseConditionsWithDate()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), '_prepareCourseConditions', array(array('date' => 'today', 'creator' => 'admin', 'nickname' => 'admin')));
        $this->assertEquals(strtotime('today'), $result['startTimeGreaterThan']);
    }

    public function testSearchByStudentNumAndTimeZone()
    {
        $result = $this->getCourseService()->searchByStudentNumAndTimeZone(array(), 0, 5);
        $this->assertEmpty($result);
    }

    public function testSearchByRatingAndTimeZone()
    {
        $result = $this->getCourseService()->searchByRatingAndTimeZone(array(), 0, 5);
        $this->assertEmpty($result);
    }

    public function testFindCourseTasksAndChapters()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);

        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'findTasksByCourseId', 'returnValue' => array()),
        ));
        $this->mockBiz('Course:CourseChapterDao', array(
            array('functionName' => 'findChaptersByCourseId', 'returnValue' => array(array('type' => 'lesson'), array('type' => 'chapter'))),
        ));

        $result = $this->getCourseService()->findCourseTasksAndChapters($defaultCourse['id']);
        $this->assertEquals('chapter', $result[0]['itemType']);
    }

    public function testConvertTasks()
    {
        $this->mockBiz('Course:CourseSetService', array(
            array('functionName' => 'getCourseSet', 'returnValue' => array('summary' => 'test Summary')),
        ));
        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'findActivities', 'returnValue' => array(
                array('id' => 1, 'ext' => array('mediaId' => 1, 'replayStatus' => 'draft', 'liveProvider' => 'test'), 'content' => 'test Content1'),
                array('id' => 2, 'ext' => array('mediaId' => 1, 'replayStatus' => 'draft', 'liveProvider' => 'test'), 'content' => 'test Content2'),
            )),
        ));
        $this->mockBiz('Task:TaskResultService', array(
            array('functionName' => 'countTaskResults', 'returnValue' => array(1)),
        ));
        $this->mockBiz('Course:CourseChapterDao', array(
            array('functionName' => 'findChaptersByCourseId', 'returnValue' => array(array('type' => 'lesson', 'seq' => 1), array('type' => 'unit', 'seq' => 2))),
        ));

        $result = $this->getCourseService()->convertTasks(
            array(
                array('id' => 1, 'activityId' => 1, 'type' => 'live', 'isFree' => 1, 'createdUserId' => 1, 'categoryId' => 1),
                array('id' => 2, 'activityId' => 2, 'type' => 'doc', 'isFree' => 1, 'createdUserId' => 1, 'categoryId' => 1),
            ),
            array('type' => 'live', 'summary' => '', 'id' => 9, 'courseSetId' => 5)
        );
        $this->assertEquals('live', $result[0]['type']);
        $this->assertEquals('draft', $result[0]['replayStatus']);
        $this->assertEquals('test Summary', $result[0]['summary']);
        $this->assertEquals('test Content1', $result[0]['content']);
        $this->assertEquals(1, $result[0]['memberNum'][0]);
    }

    public function testFilledTaskByActivity()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'filledTaskByActivity', array(
            array('type' => 'video'),
            array('ext' => array('mediaSource' => 'add', 'mediaUri' => '/')),
        ));
        $this->assertEquals('add', $result['mediaSource']);

        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'filledTaskByActivity', array(
            array('type' => 'audio'),
            array('ext' => array('hasText' => 'test Text'), 'content' => 'test Content'),
        ));
        $this->assertEquals('self', $result['mediaSource']);
        $this->assertTrue($result['hasText']);
        $this->assertEquals('test Content', $result['mediaText']);
    }

    public function testFindUserLearningCourseCountNotInClassroom()
    {
        $count = $this->getCourseService()->findUserLearningCourseCountNotInClassroom(1);
        $this->assertEquals(0, $count);

        $count = $this->getCourseService()->findUserLearningCourseCountNotInClassroom(1, array('type' => 'live'));
        $this->assertEquals(0, $count);
    }

    public function testFindUserLearningCoursesNotInClassroom()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findMembersNotInClassroomByUserIdAndRoleAndIsLearned', 'returnValue' => array(array('courseId' => 1, 'learnedNum' => 3))),
        ));

        $result = $this->getCourseService()->findUserLearningCoursesNotInClassroom(1, 0, 5);
        $this->assertEquals(3, $result[0]['memberLearnedNum']);

        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned', 'returnValue' => array(array('courseId' => 1, 'learnedNum' => 5))),
        ));

        $result = $this->getCourseService()->findUserLearningCoursesNotInClassroom(1, 0, 5, array('type' => 'live'));
        $this->assertEquals(5, $result[0]['memberLearnedNum']);
    }

    public function testFindUserLeanedCourseCount()
    {
        $count = $this->getCourseService()->findUserLeanedCourseCount(1);
        $this->assertEquals(0, $count);

        $count = $this->getCourseService()->findUserLeanedCourseCount(1, array('type' => 'live'));
        $this->assertEquals(0, $count);
    }

    public function testFindUserLearnedCoursesNotInClassroom()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findMembersNotInClassroomByUserIdAndRoleAndIsLearned', 'returnValue' => array(array('courseId' => 1, 'learnedNum' => 3))),
        ));

        $result = $this->getCourseService()->findUserLearnedCoursesNotInClassroom(1, 0, 5);
        $this->assertEquals(3, $result[0]['memberLearnedNum']);

        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findMembersNotInClassroomByUserIdAndCourseTypeAndIsLearned', 'returnValue' => array(array('courseId' => 1, 'learnedNum' => 5))),
        ));

        $result = $this->getCourseService()->findUserLearnedCoursesNotInClassroom(1, 0, 5, array('type' => 'live'));
        $this->assertEquals(5, $result[0]['memberLearnedNum']);
    }

    public function testFindUserLearnCourseCountNotInClassroom()
    {
        $count = $this->getCourseService()->findUserLearnCourseCountNotInClassroom(1);
        $this->assertEquals(0, $count);
    }

    public function testFindUserLearnCoursesNotInClassroom()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findMembersNotInClassroomByUserIdAndRole', 'returnValue' => array(array('courseId' => 1))),
        ));

        $result = $this->getCourseService()->findUserLearnCoursesNotInClassroom(1, 0, 5);
        $this->assertEquals('第二个教学计划', $result[1]['title']);
    }

    public function testFindUserLearnCoursesNotInClassroomWithType()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findMembersNotInClassroomByUserIdAndRoleAndType', 'returnValue' => array(array('courseId' => 1))),
        ));

        $result = $this->getCourseService()->findUserLearnCoursesNotInClassroomWithType(1, 'live', 0, 5);
        $this->assertEquals('第二个教学计划', $result[1]['title']);
    }

    public function testFindUserTeachCourseCountNotInClassroom()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findMembersNotInClassroomByUserIdAndRole', 'returnValue' => array(array('courseId' => 1))),
        ));

        $count = $this->getCourseService()->findUserTeachCourseCountNotInClassroom(array('userId' => 1));
        $this->assertEquals(1, $count);
    }

    public function testFindUserTeachCoursesNotInClassroom()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);
        $this->mockBiz('Course:CourseMemberDao', array(
            array('functionName' => 'findMembersNotInClassroomByUserIdAndRole', 'returnValue' => array(array('courseId' => 1))),
        ));

        $result = $this->getCourseService()->findUserTeachCoursesNotInClassroom(array('userId' => 1), 0, 5);
        $this->assertEquals('第二个教学计划', $result[0]['title']);
    }

    public function testFindUserFavoritedCourseCountNotInClassroom()
    {
        $this->mockBiz('Course:FavoriteDao', array(
            array('functionName' => 'findCourseFavoritesNotInClassroomByUserId', 'returnValue' => array(array())),
        ));

        $count = $this->getCourseService()->findUserFavoritedCourseCountNotInClassroom(1);
        $this->assertEquals(0, $count);

        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);
        $this->mockBiz('Course:FavoriteDao', array(
            array('functionName' => 'findCourseFavoritesNotInClassroomByUserId', 'returnValue' => array(array('courseId' => 1))),
        ));

        $count = $this->getCourseService()->findUserFavoritedCourseCountNotInClassroom(1);
        $this->assertEquals(1, $count);
    }

    public function testFindUserFavoritedCoursesNotInClassroom()
    {
        $this->mockBiz('Course:FavoriteDao', array(
            array('functionName' => 'findCourseFavoritesNotInClassroomByUserId', 'returnValue' => array(array('courseId' => 1))),
        ));
        $this->mockBiz('Course:CourseDao', array(
            array('functionName' => 'search', 'returnValue' => array(array('id' => 1))),
        ));
        $result = $this->getCourseService()->findUserFavoritedCoursesNotInClassroom(1, 0, 5);
        $this->assertEquals(1, $result[0]['id']);
    }

    public function testFindUserFavoriteCoursesNotInClassroomWithCourseType()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->mockBiz('Course:FavoriteDao', array(
            array('functionName' => 'findUserFavoriteCoursesNotInClassroomWithCourseType', 'returnValue' => array(array('id' => 1))),
        ));
        $result = $this->getCourseService()->findUserFavoriteCoursesNotInClassroomWithCourseType(1, 'live', 0, 5);
        $this->assertEquals('第二个教学计划', $result[1]['title']);
    }

    public function testCountUserFavoriteCourseNotInClassroomWithCourseType()
    {
        $this->mockBiz('Course:FavoriteDao', array(
            array('functionName' => 'countUserFavoriteCoursesNotInClassroomWithCourseType', 'returnValue' => 1),
        ));

        $count = $this->getCourseService()->countUserFavoriteCourseNotInClassroomWithCourseType(1, 'live');
        $this->assertEquals(1, $count);
    }

    public function testBatchConvert()
    {
        $activities = array(
            array(
                'id' => 1,
                'title' => 'activity111',
                'mediaType' => 'video',
                'ext' => array(
                    'id' => 2,
                    'mediaSource' => 'self',
                    'mediaId' => '22',
                    'mediaUri' => '',
                    'file' => array(
                        'id' => 4,
                        'globalId' => '8270bc5fa3f94d29afd957d42bb5393b',
                        'hashId' => 'course-activity-1409/20171204024002-feoht1gxeko4k8g0',
                        'targetType' => 'course-activity',
                    ),
                ),
            ),
        );

        $this->mockBiz('Activity:ActivityService');
        $this->getActivityService()->shouldReceive('findActivitiesByCourseIdAndType')->andReturn($activities);
        $this->mockBiz('File:UploadFileService');
        $this->getUploadFileService()->shouldReceive('batchConvertByIds');

        $this->getCourseService()->batchConvert(1);

        $this->getActivityService()->shouldHaveReceived('findActivitiesByCourseIdAndType');
        $this->getUploadFileService()->shouldHaveReceived('batchConvertByIds');
    }

    public function testValidatie()
    {
        $fileds = array(
            'enableAudio' => '1',
        );

        $courseFields = array(
            'id' => 2,
            'courseSetId' => 2,
            'title' => '计划名称',
            'enableAudio' => '0',
            'learnMode' => 'lockMode',
            'expiryDays' => 0,
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );
        $course = $this->getCourseService()->createCourse($courseFields);

        $this->mockBiz('File:UploadFileService');
        $this->getUploadFileService()->shouldReceive('getAudioServiceStatus')->andReturn('notAllowed');

        ReflectionUtils::invokeMethod($this->getCourseService(), 'validatie', array($course['id'], &$fileds));

        $this->getUploadFileService()->shouldReceive('getAudioServiceStatus');
    }

    public function testConverAudioByCourseIdAndMediaIdunableAudio()
    {
        $fields = array(
            'id' => 2,
            'courseSetId' => 2,
            'title' => '计划名称',
            'enableAudio' => '0',
            'learnMode' => 'lockMode',
            'expiryDays' => 0,
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );
        $course = $this->getCourseService()->createCourse($fields);

        $result = $this->getCourseService()->convertAudioByCourseIdAndMediaId($course['id'], 1);

        $this->assertEquals($result, false);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     */
    public function testConverAudioByCourseIdAndMediaIdEmptyMedia()
    {
        $fields = array(
            'id' => 2,
            'courseSetId' => 2,
            'title' => '计划名称',
            'enableAudio' => '1',
            'learnMode' => 'lockMode',
            'expiryDays' => 0,
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );
        $course = $this->getCourseService()->createCourse($fields);

        $this->mockBiz('System:SettingService');
        $this->getSettingService()->shouldReceive('get')->andReturn(array(
            'upload_mode' => 'cloud',
        ));

        $this->mockBiz('File:UploadFileService');
        $this->getUploadFileService()->shouldReceive('getFile')->andReturn(array());

        $result = $this->getCourseService()->convertAudioByCourseIdAndMediaId($course['id'], 1);

        $this->getSettingService()->shouldHaveReceived('get');
        $this->getUploadFileService()->shouldHaveReceived('getFile');
    }

    public function testConverAudioByCourseIdAndMediaIdLocal()
    {
        $fields = array(
            'id' => 2,
            'courseSetId' => 2,
            'title' => '计划名称',
            'enableAudio' => '1',
            'learnMode' => 'lockMode',
            'expiryDays' => 0,
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );
        $course = $this->getCourseService()->createCourse($fields);

        $this->mockBiz('System:SettingService');
        $this->getSettingService()->shouldReceive('get')->andReturn(array(
            'upload_mode' => 'cloud',
        ));

        $this->mockBiz('File:UploadFileService');
        $this->getUploadFileService()->shouldReceive('getFile')->andReturn(array(
            'id' => 1,
            'globalId' => 'f9bda3613f8447c39e96975629bff701',
            'targetType' => 'courselesson',
            'storage' => 'local',
        ));

        $result = $this->getCourseService()->convertAudioByCourseIdAndMediaId($course['id'], 1);

        $this->getSettingService()->shouldHaveReceived('get');
        $this->getUploadFileService()->shouldHaveReceived('getFile');

        $this->assertEquals($result, false);
    }

    public function testConverAudioByCourseIdAndMediaId()
    {
        $fields = array(
            'id' => 2,
            'courseSetId' => 2,
            'title' => '计划名称',
            'enableAudio' => '1',
            'learnMode' => 'lockMode',
            'expiryDays' => 0,
            'expiryMode' => 'forever',
            'courseType' => 'normal',
        );
        $course = $this->getCourseService()->createCourse($fields);

        $this->mockBiz('System:SettingService');
        $this->getSettingService()->shouldReceive('get')->andReturn(array(
            'upload_mode' => 'cloud',
        ));

        $this->mockBiz('File:UploadFileService');
        $this->getUploadFileService()->shouldReceive('getFile')->andReturn(array(
            'id' => 1,
            'globalId' => 'f9bda3613f8447c39e96975629bff701',
            'targetType' => 'courselesson',
            'storage' => 'cloud',
            'audioConvertStatus' => 'none',
        ));

        $this->getUploadFileService()->shouldReceive('retryTranscode');
        $this->getUploadFileService()->shouldReceive('setAudioConvertStatus');

        $result = $this->getCourseService()->convertAudioByCourseIdAndMediaId($course['id'], 1);

        $this->getSettingService()->shouldHaveReceived('get');
        $this->getUploadFileService()->shouldHaveReceived('getFile');
        $this->getUploadFileService()->shouldHaveReceived('retryTranscode');
        $this->getUploadFileService()->shouldHaveReceived('setAudioConvertStatus');

        $this->assertEquals($result, true);
    }

    public function testFindLiveCourse()
    {
        $this->mockBiz(
            'Task:TaskService',
            array(
                array(
                    'functionName' => 'searchTasks',
                    'returnValue' => array(
                        array('id' => 2, 'courseId' => 2, 'title' => 'title', 'startTime' => 6000, 'endTime' => 7000),
                        array('id' => 3, 'courseId' => 3, 'title' => 'title', 'startTime' => 7000, 'endTime' => 8000),
                    ),
                    'withParams' => array(
                        array('type' => 'live', 'startTime_GE' => 5000, 'endTime_LT' => 10000, 'status' => 'published'),
                        array(),
                        0,
                        PHP_INT_MAX,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseMemberDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array()),
                    'withParams' => array(array('courseId' => 2, 'role' => 'teacher'), array(), 0, PHP_INT_MAX),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 2, 'userId' => 2)),
                    'withParams' => array(array('courseId' => 3, 'role' => 'teacher'), array(), 0, PHP_INT_MAX),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 3, 'status' => 'published', 'title' => 'title', 'courseSetId' => 3),
                    'withParams' => array(3),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseSetDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 3, 'status' => 'published', 'title' => 'title'),
                    'withParams' => array(3),
                ),
            )
        );
        $result = $this->getCourseService()->findLiveCourse(
            array('startTime_GE' => 5000, 'endTime_LT' => 10000),
            2,
            'teacher'
        );
        $this->assertEquals('title', $result[0]['title']);
    }

    //recommendCount=2,offset=0,limit=2，整页都是推荐课程
    public function testSearchByRecommendedSeqCondition1()
    {
        $this->createRecommendCourses();
        $result = $this->getCourseService()->searchByRecommendedSeq(array('status' => 'published', 'courseSetStatus' => 'published'), array('recommendedSeq' => 'ASC'), 0, 2);
        $this->assertEquals('课程标题1', $result[0]['courseSetTitle']);
        $this->assertEquals('课程标题2', $result[1]['courseSetTitle']);
    }

    //recommendCount=2,offset=2,limit=2，整页都不是推荐课程
    public function testSearchByRecommendedSeqCondition2()
    {
        $this->createRecommendCourses();
        $result = $this->getCourseService()->searchByRecommendedSeq(array('status' => 'published', 'courseSetStatus' => 'published'), array('recommendedSeq' => 'ASC'), 2, 2);
        $this->assertEquals('课程标题4', $result[0]['courseSetTitle']);
        $this->assertEquals('课程标题4', $result[1]['courseSetTitle']);
    }

    //recommendCount=2,offset=1,limit=2，既有推荐也有非推荐课程
    public function testSearchByRecommendedSeqCondition3()
    {
        $this->createRecommendCourses();
        $result = $this->getCourseService()->searchByRecommendedSeq(array('status' => 'published', 'courseSetStatus' => 'published'), array('recommendedSeq' => 'ASC'), 1, 2);
        $this->assertEquals('课程标题2', $result[0]['courseSetTitle']);
        $this->assertEquals('课程标题4', $result[1]['courseSetTitle']);
    }

    private function createRecommendCourses()
    {
        TimeMachine::setMockedTime(time());
        $courseSetFields = array(
            'title' => '课程标题1',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);
        $this->getCourseService()->recommendCourseByCourseSetId($courseSet['id'], array(
            'recommended' => 1,
            'recommendedTime' => TimeMachine::time(),
            'recommendedSeq' => 1,
        ));
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        $courseSetFields = array(
            'title' => '课程标题2',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);
        $this->getCourseService()->recommendCourseByCourseSetId($courseSet['id'], array(
            'recommended' => 1,
            'recommendedTime' => TimeMachine::time(),
            'recommendedSeq' => 2,
        ));
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        //课程和计划未发布
        $courseSetFields = array(
            'title' => '课程标题3',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);
        $this->getCourseService()->recommendCourseByCourseSetId($courseSet['id'], array(
            'recommended' => 1,
            'recommendedTime' => TimeMachine::time(),
            'recommendedSeq' => 3,
        ));

        $courseSetFields = array(
            'title' => '课程标题4',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);

        $courseSetFields = array(
            'title' => '课程标题4',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);
        $this->getCourseSetService()->publishCourseSet($courseSet['id']);
    }

    public function testCountCourseItems()
    {
        $mockedCourseChapterDao = $this->mockBiz('Course:CourseChapterDao', array(
            array(
                'functionName' => 'count',
                'withParams' => array(
                    array(
                        'courseId' => 123,
                        'types' => array('chapter', 'unit'),
                    ),
                ),
                'returnValue' => 2,
            ),
        ));

        $mockedTaskService = $this->mockBiz('Task:TaskService', array(
            array(
                'functionName' => 'countLessonsWithMultipleTasks',
                'withParams' => array(123),
                'returnValue' => array(2, 3, 4),
            ),
        ));

        $courseInfo = array(
            'id' => 123,
            'compulsoryTaskNum' => 3,
        );
        $result = $this->getCourseService()->countCourseItems($courseInfo);

        $this->assertEquals(8, $result);
    }

    public function testAppendReservationConditionsWithClosed()
    {
        $this->mockBiz('System:SettingService');
        $this->getSettingService()->shouldReceive('isReservationOpen')->andReturn(false);

        $conditions = array();
        $conditions = $this->getCourseService()->appendReservationConditions($conditions);

        $this->getSettingService()->shouldHaveReceived('isReservationOpen');
        $this->assertEquals('reservation', $conditions['excludeTypes'][0]);
    }

    public function testAppendReservationConditionsWithOpen()
    {
        $this->mockBiz('System:SettingService');
        $this->getSettingService()->shouldReceive('isReservationOpen')->andReturn(true);

        $conditions = array();
        $conditions = $this->getCourseService()->appendReservationConditions($conditions);

        $this->getSettingService()->shouldHaveReceived('isReservationOpen');
        $this->assertTrue(empty($conditions['excludeTypes']));
    }

    public function testCountCoursesByCourseSetId()
    {
        $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);

        $count = $this->getCourseService()->countCoursesByCourseSetId(1);
        $this->assertEquals(1, $count);
    }

    public function testGetFavoritedCourseByUserIdAndCourseSetId()
    {
        $this->mockBiz('Course:FavoriteDao', array(
            array('functionName' => 'getByUserIdAndCourseSetId', 'returnValue' => array('courseId' => 1)),
        ));

        $result = $this->getCourseService()->getFavoritedCourseByUserIdAndCourseSetId(1, 1);
        $this->assertEquals(1, $result['courseId']);
    }

    public function testCalculateLearnProgressByUserIdAndCourseIds()
    {
        $defaultCourse = $this->createDefaultCourse('第二个教学计划', array('id' => 1), 0);
        $this->getCourseService()->publishCourse($defaultCourse['id']);
        $this->mockBiz('Course:MemberService', array(
            array('functionName' => 'countMembers', 'returnValue' => 1),
            array('functionName' => 'searchMembers', 'returnValue' => array(array('courseId' => 1, 'learnedNum' => 4))),
        ));

        $result = $this->getCourseService()->calculateLearnProgressByUserIdAndCourseIds(1, array(1));
        $this->assertEquals(4, $result[0]['learnedNum']);
    }

    public function testBuildCourseExpiryDataFromClassroom()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'buildCourseExpiryDataFromClassroom', array(
            'days',
            0,
        ));
        $this->assertEquals('forever', $result['expiryMode']);

        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'buildCourseExpiryDataFromClassroom', array(
            'days',
            1,
        ));
        $this->assertEquals(1, $result['expiryDays']);

        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'buildCourseExpiryDataFromClassroom', array(
            'date',
            1,
        ));
        $this->assertEquals('end_date', $result['expiryMode']);
    }

    public function testFillCourseTryLookVideo()
    {
        $this->mockBiz('Activity:ActivityService', array(
            array('functionName' => 'findActivitySupportVideoTryLook', 'returnValue' => array(
                array('id' => 1, 'fromCourseId' => 1),
            )),
        ));
        $this->mockBiz('Task:TaskService', array(
            array('functionName' => 'findTasksByActivityIds', 'returnValue' => array(array('activityId' => 1, 'status' => 'published'))),
        ));

        $result = $this->getCourseService()->fillCourseTryLookVideo(
            array(
                array('status' => 'published', 'tryLookable' => 1, 'id' => 1),
                array('status' => 'published', 'tryLookable' => 1, 'id' => 2),
            )
        );
        $this->assertEquals(1, $result[0]['tryLookVideo']);
        $this->assertEquals(0, $result[1]['tryLookVideo']);
    }

    public function testPrepareUserLearnCondition()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'prepareUserLearnCondition', array(
            1,
            array('type' => 'live', 'classroomId' => 1, 'locked' => 1),
        ));
        $this->assertEquals('live', $result['c.type']);
        $this->assertEquals(1, $result['m.classroomId']);
        $this->assertEquals(1, $result['m.locked']);
    }

    public function testProcessFields()
    {
        $result = ReflectionUtils::invokeMethod($this->getCourseService(), 'processFields', array(
            array('status' => 'published'),
            array('buyExpiryTime' => time(), 'about' => 'about', 'summary' => 'summary', 'goals' => 1, 'audiences' => 1, 'tryLookLength' => 10),
            array('status' => 'published', 'type' => 'normal'),
        ));
        $this->assertEquals('about', $result['about']);
        $this->assertEquals(1, $result['goals']);
        $this->assertEquals(10, $result['tryLookLength']);
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

    protected function defaultCourse($title, $courseSet, $isDefault = 1)
    {
        return array(
            'title' => $title,
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => $isDefault,
            'courseType' => $isDefault ? 'default' : 'normal',
        );
    }

    protected function createDefaultCourse($title, $courseSet, $isDefault = 1)
    {
        $course = $this->defaultCourse($title, $courseSet, $isDefault);

        return $this->getCourseService()->createCourse($course);
    }

    protected function createTask($type, $courseId)
    {
        $field = array(
            'mode' => 'lesson',
            'courseId' => $courseId,
            'title' => 'task title',
            'seq' => '1',
            'type' => 'video',
            'activityId' => '1',
            'mediaSource' => 'self',
            'isFree' => '0',
            'isOptional' => '0',
            'startTime' => '0',
            'endTime' => '0',
            'length' => '300',
            'status' => 'create',
            'createdUserId' => '1',
        );
        if ('default' == $type) {
            $task = $this->getDefaultStrategy()->createTask($field);
        } else {
            $task = $this->getNormalStrategy()->createTask($field);
        }

        return $task;
    }

    protected function createActivity($fields)
    {
        $fields = array_merge($this->getDefaultMockFields(), $fields);

        return $this->getActivityDao()->create($fields);
    }

    protected function createChapter($courseId, $title)
    {
        $fields = array(
            'courseId' => $courseId,
            'title' => $title,
            'type' => 'lesson',
            'status' => 'created',
        );

        return $this->getCourseService()->createChapter($fields);
    }

    protected function getDefaultMockFields()
    {
        return array(
            'title' => 'activity',
            'mediaId' => 0,
            'mediaType' => 'text',
            'content' => '124',
            'fromCourseId' => 1,
            'fromCourseSetId' => 1,
            'fromUserId' => 1,
            'startTime' => time() - 1000,
            'endTime' => time(),
        );
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

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return ActivityDao
     */
    private function getActivityDao()
    {
        return $this->createDao('Activity:ActivityDao');
    }

    /**
     * @return CourseMemberDao
     */
    protected function getMemberDao()
    {
        return $this->createDao('Course:CourseMemberDao');
    }

    private function getDefaultStrategy()
    {
        return new DefaultStrategy($this->biz);
    }

    private function getNormalStrategy()
    {
        return new NormalStrategy($this->biz);
    }
}
