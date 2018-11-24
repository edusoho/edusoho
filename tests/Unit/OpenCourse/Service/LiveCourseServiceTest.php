<?php

namespace Tests\Unit\OpenCourse\Service;

use Biz\BaseTestCase;
use Mockery;
use Biz\Util\EdusohoLiveClient;
use Biz\User\CurrentUser;

class LiveCourseServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\Activity\LiveActivityException
     * @expectedExceptionMessage exception.live_activity.create_liveroom_failed
     */
    public function testCreateLiveRoomEmpty()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('live_logo' => 'http://www.edusoho.com'),
            ),
        ));

        $mockObject = $this->_mockLiveCient();
        $mockObject->shouldReceive('createLive')->times(1)->andReturn(array());
        $this->getLiveCourseService()->setLiveClient($mockObject);

        $user = $this->getCurrentuser();
        $lesson = array('id' => 1, 'title' => 'lesson title', 'type' => 'video', 'courseId' => 1, 'startTime' => strtotime('+1 day'), 'length' => 30);
        $routes = array('authUrl' => 'http://www.edusoho.com', 'jumpUrl' => 'http://www.qiqiuyu.com');

        $this->getLiveCourseService()->createLiveRoom(array('teacherIds' => array($user['id'])), $lesson, $routes);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     * @expectedExceptionMessage create liveroom error
     */
    public function testCreateLiveRoomError()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('live_logo' => 'http://www.edusoho.com'),
            ),
        ));

        $mockObject = $this->_mockLiveCient();
        $mockObject->shouldReceive('createLive')->times(1)->andReturn(array('error' => 'create liveroom error'));
        $this->getLiveCourseService()->setLiveClient($mockObject);

        $user = $this->getCurrentuser();
        $lesson = array('id' => 1, 'title' => 'lesson title', 'type' => 'video', 'courseId' => 1, 'startTime' => strtotime('+1 day'), 'length' => 30);
        $routes = array('authUrl' => 'http://www.edusoho.com', 'jumpUrl' => 'http://www.qiqiuyu.com');

        $this->getLiveCourseService()->createLiveRoom(array('teacherIds' => array($user['id'])), $lesson, $routes);
    }

    public function testCreateLiveRoom()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('live_logo' => 'http://www.edusoho.com'),
            ),
        ));

        $mockValue = array('id' => 1, 'provider' => 'kuozhi');
        $mockObject = $this->_mockLiveCient();
        $mockObject->shouldReceive('createLive')->times(1)->andReturn($mockValue);
        $this->getLiveCourseService()->setLiveClient($mockObject);

        $user = $this->getCurrentuser();
        $lesson = array('id' => 1, 'title' => 'lesson title', 'type' => 'video', 'courseId' => 1, 'startTime' => strtotime('+1 day'), 'length' => 30);
        $routes = array('authUrl' => 'http://www.edusoho.com', 'jumpUrl' => 'http://www.qiqiuyu.com');

        $result = $this->getLiveCourseService()->createLiveRoom(array('teacherIds' => array($user['id'])), $lesson, $routes);

        $this->assertEquals($mockValue['id'], $result['id']);
        $this->assertEquals($mockValue['provider'], $result['provider']);
    }

    public function testEditLiveRoom()
    {
        $mockValue = array('id' => 1, 'title' => 'live title', 'provider' => 'kuozhi');
        $mockObject = $this->_mockLiveCient();
        $mockObject->shouldReceive('updateLive')->times(1)->andReturn($mockValue);
        $this->getLiveCourseService()->setLiveClient($mockObject);

        $user = $this->getCurrentuser();
        $lesson = array('id' => 1, 'title' => 'lesson title', 'type' => 'video', 'courseId' => 1, 'startTime' => strtotime('+1 day'), 'length' => 30, 'mediaId' => 10, 'liveProvider' => 'kuozhi');
        $routes = array('authUrl' => 'http://www.edusoho.com', 'jumpUrl' => 'http://www.qiqiuyu.com');

        $result = $this->getLiveCourseService()->editLiveRoom(array('teacherIds' => array($user['id'])), $lesson, $routes);

        $this->assertEquals($mockValue['id'], $result['id']);
        $this->assertEquals($mockValue['provider'], $result['provider']);
        $this->assertEquals($mockValue['title'], $result['title']);
    }

    public function testEntryLive()
    {
        $mockValue = array('success' => true);
        $mockObject = $this->_mockLiveCient();
        $mockObject->shouldReceive('entryLive')->times(1)->andReturn($mockValue);
        $this->getLiveCourseService()->setLiveClient($mockObject);

        $result = $this->getLiveCourseService()->entryLive(array('userId' => 1));

        $this->assertTrue($result['success']);
    }

    public function testCheckLessonStatus()
    {
        $result = $this->getLiveCourseService()->checkLessonStatus(array());
        $this->assertFalse($result['result']);
        $this->assertEquals('课时不存在！', $result['message']);

        $result = $this->getLiveCourseService()->checkLessonStatus(array('id' => 1, 'mediaId' => 0));
        $this->assertFalse($result['result']);
        $this->assertEquals('直播教室不存在！', $result['message']);

        $result = $this->getLiveCourseService()->checkLessonStatus(array('id' => 1, 'mediaId' => 10, 'startTime' => strtotime('+5 days')));
        $this->assertFalse($result['result']);
        $this->assertEquals('直播还没开始!', $result['message']);

        $result = $this->getLiveCourseService()->checkLessonStatus(array('id' => 1, 'mediaId' => 10, 'startTime' => (time() - 3600), 'endTime' => (time() - 1000), 'liveProvider' => 4));
        $this->assertFalse($result['result']);
        $this->assertEquals('直播已结束!', $result['message']);

        $result = $this->getLiveCourseService()->checkLessonStatus(array('id' => 1, 'mediaId' => 10, 'startTime' => (time() - 3600 * 4), 'endTime' => (time() - 3600 * 3), 'liveProvider' => 8));
        $this->assertFalse($result['result']);
        $this->assertEquals('直播已结束!', $result['message']);

        $result = $this->getLiveCourseService()->checkLessonStatus(array('id' => 1, 'mediaId' => 10, 'startTime' => (time() + 30), 'endTime' => (time() + 3600), 'liveProvider' => 4));
        $this->assertTrue($result['result']);
        $this->assertEmpty($result['message']);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.is_not_member
     */
    public function testCheckCourseUserRoleMemberEmpty()
    {
        $user = $this->getCurrentuser();
        $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array(),
            ),
        ));

        $course = array('id' => 1, 'teacherIds' => array($user['id'], 3));
        $lesson = array('id' => 2, 'courseId' => 1, 'type' => 'liveOpen');

        $this->getLiveCourseService()->checkCourseUserRole($course, $lesson);
    }

    public function testCheckCourseUserRole()
    {
        $user = $this->getCurrentuser();
        $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array('id' => 1, 'courseId' => 1, 'userId' => $user['id']),
            ),
            array(
                'functionName' => 'findCourseTeachers',
                'returnValue' => array(array('id' => 1, 'userId' => $user['id']), array('id' => 2, 'userId' => 3)),
            ),
        ));

        $course = array('id' => 1, 'teacherIds' => array($user['id'], 3));
        $lesson = array('id' => 2, 'courseId' => 1, 'type' => 'liveOpen');
        $role = $this->getLiveCourseService()->checkCourseUserRole($course, $lesson);

        $this->assertEquals('teacher', $role);
    }

    public function testCheckCourseUserRoleSpeaker()
    {
        $user = $this->getCurrentuser();
        $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array('id' => 1, 'courseId' => 1, 'userId' => $user['id']),
            ),
            array(
                'functionName' => 'findCourseTeachers',
                'returnValue' => array(array('id' => 1, 'userId' => $user['id']), array('id' => 2, 'userId' => 3)),
            ),
        ));

        $course = array('id' => 1, 'teacherIds' => array(3, $user['id']));
        $lesson = array('id' => 2, 'courseId' => 1, 'type' => 'liveOpen');
        $role = $this->getLiveCourseService()->checkCourseUserRole($course, $lesson);

        $this->assertEquals('speaker', $role);
    }

    public function testCheckCourseUserRoleStudent()
    {
        $user = $this->getCurrentuser();
        $this->mockBiz('OpenCourse:OpenCourseService', array(
            array(
                'functionName' => 'getCourseMember',
                'returnValue' => array('id' => 1, 'courseId' => 1, 'userId' => $user['id']),
            ),
            array(
                'functionName' => 'findCourseTeachers',
                'returnValue' => array(array('id' => 1, 'userId' => 3), array('id' => 2, 'userId' => 5)),
            ),
        ));

        $course = array('id' => 1, 'teacherIds' => array(3, 5));
        $lesson = array('id' => 2, 'courseId' => 1, 'type' => 'liveOpen');
        $role = $this->getLiveCourseService()->checkCourseUserRole($course, $lesson);

        $this->assertEquals('student', $role);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testCheckCourseUserRoleUnLogin()
    {
        $this->setAnonymousUser();

        $course = array('id' => 1, 'teacherIds' => array(3, 5));
        $lesson = array('id' => 2, 'courseId' => 1, 'type' => 'liveOpen');
        $role = $this->getLiveCourseService()->checkCourseUserRole($course, $lesson);

        $this->assertEquals('student', $role);

        $lesson = array('id' => 2, 'courseId' => 1, 'type' => 'video');
        $this->getLiveCourseService()->checkCourseUserRole($course, $lesson);
    }

    public function testIsLiveFinishedLessonEmpty()
    {
        $result = $this->getLiveCourseService()->isLiveFinished(1);
        $this->assertTrue($result);

        $this->mockBiz('OpenCourse:OpenCourseLessonDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'type' => 'video'),
            ),
        ));
        $result = $this->getLiveCourseService()->isLiveFinished(1);
        $this->assertTrue($result);
    }

    public function testIsLiveFinishedThirdLiveProvider()
    {
        $startTime = time() - 3600;
        $endTime = time() - 1800;
        $this->mockBiz('OpenCourse:OpenCourseLessonDao', array(
            array(
                'functionName' => 'get',
                'withParams' => array(1),
                'returnValue' => array('id' => 1, 'mediaId' => 1, 'type' => 'liveOpen', 'startTime' => $startTime, 'endTime' => $endTime, 'liveProvider' => 1, 'progressStatus' => 'created'),
            ),
        ));

        $result = $this->getLiveCourseService()->isLiveFinished(1);

        $this->assertTrue($result);
    }

    public function testIsLiveFinishedEsLive()
    {
        $startTime1 = time() - 3600 * 4;
        $endTime1 = time() - 3600 * 3;

        $startTime2 = time() - 3600;
        $endTime2 = time() - 1800;
        $this->mockBiz('OpenCourse:OpenCourseLessonDao', array(
            array(
                'functionName' => 'get',
                'withParams' => array(1),
                'returnValue' => array('id' => 1, 'mediaId' => 1, 'type' => 'liveOpen', 'startTime' => $startTime1, 'endTime' => $endTime1, 'liveProvider' => 9, 'progressStatus' => 'created'),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array(2),
                'returnValue' => array('id' => 2, 'mediaId' => 2, 'type' => 'liveOpen', 'startTime' => $startTime2, 'endTime' => $endTime2, 'liveProvider' => 8, 'progressStatus' => 'created'),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array(3),
                'returnValue' => array('id' => 3, 'mediaId' => 3, 'type' => 'liveOpen', 'startTime' => $startTime2, 'endTime' => $endTime2, 'liveProvider' => 9, 'progressStatus' => 'closed'),
            ),
        ));

        $result = $this->getLiveCourseService()->isLiveFinished(1);
        $this->assertTrue($result);

        $result = $this->getLiveCourseService()->isLiveFinished(2);
        $this->assertFalse($result);

        $result = $this->getLiveCourseService()->isLiveFinished(3);
        $this->assertTrue($result);
    }

    protected function _mockLiveCient()
    {
        $api = new EdusohoLiveClient();
        $mockObject = Mockery::mock($api);

        return $mockObject;
    }

    protected function setAnonymousUser()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));
        $biz = $this->getBiz();
        $biz['user'] = $currentUser;
    }

    protected function getLiveCourseService()
    {
        return $this->createService('OpenCourse:LiveCourseService');
    }
}
