<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MemberService;
use Biz\Course\Service\CourseSetService;
use Biz\Classroom\Service\ClassroomService;
use AppBundle\Common\ReflectionUtils;

class CourseServiceTest extends BaseTestCase
{
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

    public function testCreateCourse()
    {
        $course = $this->defaultCourse('默认教学计划', array('id' => 1));
        $result = $this->getCourseService()->createCourse($course);

        $this->assertEquals($result['title'], $course['title']);
        $this->assertEquals($result['courseType'], $course['courseType']);
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
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
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
}
