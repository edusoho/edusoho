<?php

namespace Tests\Unit\OpenCourse\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Content\Service\FileService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OpenCourseServiceTest extends BaseTestCase
{
    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.permission_denied
     */
    public function testCreateCourseUnlogin()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'liveOpenCourse',
            'type' => 'liveOpen',
            'userId' => 1,
            'createdTime' => time(),
        );

        $this->getOpenCourseService()->createCourse($course);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.permission_denied
     */
    public function testCreateCoursePermissionDeny()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 1,
            'nickname' => '测试用户',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
            'org' => array('id' => 1),
        ));

        $permissions = array(
            'admin_course_content_manage' => false,
        );
        $currentUser->setPermissions($permissions);

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'liveOpenCourse',
            'type' => 'liveOpen',
            'userId' => 1,
            'createdTime' => time(),
        );

        $this->getOpenCourseService()->createCourse($course);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateCourseParamMissing()
    {
        $course = array(
            'type' => 'liveOpen',
            'userId' => 1,
            'createdTime' => time(),
        );

        $this->getOpenCourseService()->createCourse($course);
    }

    public function testCreateCourse()
    {
        $time = time();
        $course = array(
            'title' => 'liveOpenCourse',
            'type' => 'liveOpen',
            'userId' => 1,
            'createdTime' => $time,
        );

        $excepted = array(
            'id' => '1',
            'title' => 'liveOpenCourse',
            'subtitle' => '',
            'status' => 'draft',
            'type' => 'liveOpen',
            'lessonNum' => '0',
            'categoryId' => '0',
            'tags' => array(),
            'smallPicture' => '',
            'middlePicture' => '',
            'largePicture' => '',
            'about' => '',
            'teacherIds' => array(1),
            'studentNum' => '0',
            'hitNum' => '0',
            'likeNum' => '0',
            'postNum' => '0',
            'userId' => '1',
            'parentId' => '0',
            'locked' => '0',
            'recommended' => '0',
            'recommendedSeq' => '0',
            'recommendedTime' => '0',
            'createdTime' => (string) $time,
            'updatedTime' => (string) $time,
            'orgId' => '1',
            'orgCode' => '1.',
        );

        $created = $this->getOpenCourseService()->createCourse($course);
        $this->assertEquals($excepted, $created);
    }

    /**
     * open_course.
     */
    public function testGetCourse()
    {
        $course = $this->_createLiveOpenCourse();

        $result = $this->getOpenCourseService()->getCourse($course['id']);

        $this->assertEquals($course['title'], $result['title']);
        $this->assertEquals($course['type'], $result['type']);
    }

    public function testFindCoursesByIds()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $ids = array($course1['id'], $course2['id']);

        $result = $this->getOpenCourseService()->findCoursesByIds($ids);

        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);
        $this->assertEquals($result[0]['title'], $course1['title']);
        $this->assertEquals($result[1]['title'], $course2['title']);
    }

    public function testSearchCourses()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $courses = $this->getOpenCourseService()->searchCourses(array('type' => 'liveOpen'), array('createdTime' => 'DESC'), 0, 1);

        $this->assertNotEmpty($courses);
        $this->assertEquals($courses[0]['title'], $course1['title']);
    }

    public function testSearchCourseCount()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $courseCount = $this->getOpenCourseService()->countCourses(array('type' => 'liveOpen'));

        $this->assertEquals(1, $courseCount);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testUpdateCourseNotFoundException()
    {
        $this->getOpenCourseService()->updateCourse(1, array());
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testTryManageOpenCourseUnLogin()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getOpenCourseService()->tryManageOpenCourse(1);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testTryManageOpenCourseNotFoundOpenCourse()
    {
        $this->getOpenCourseService()->tryManageOpenCourse(2333);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.forbidden_manage_course
     */
    public function testTryManageOpenCourseForbidden()
    {
        $course = $this->_createOpenCourse();

        $user = $this->getUserService()->register(array(
            'nickname' => 'user',
            'email' => 'user@user.com',
            'password' => 'user',
            'createdIp' => '127.0.0.1',
            'orgCode' => '1.',
            'orgId' => '1',
        ));

        $user['currentIp'] = $user['createdIp'];
        $user['org'] = array('id' => 1);
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getOpenCourseService()->tryManageOpenCourse($course['id']);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testTryAdminCourseNotFoundException()
    {
        $this->getOpenCourseService()->tryAdminCourse(1);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testTryAdminCourseUnLoginException()
    {
        $course = $this->_createOpenCourse();
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getOpenCourseService()->tryAdminCourse($course['id']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.permission_denied
     */
    public function testTryAdminCoursePermissionDeny()
    {
        $course = $this->_createOpenCourse();
        $user = $this->getUserService()->register(array(
            'nickname' => 'user',
            'email' => 'user@user.com',
            'password' => 'user',
            'createdIp' => '127.0.0.1',
            'orgCode' => '1.',
            'orgId' => '1',
        ));

        $user['currentIp'] = $user['createdIp'];
        $user['org'] = array('id' => 1);
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getOpenCourseService()->tryAdminCourse($course['id']);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testChangeCoursePictureNotFoundException()
    {
        $this->getOpenCourseService()->changeCoursePicture(1, array());
    }

    public function testChangeCoursePicture()
    {
        $course = $this->_createOpenCourse();

        $sourceFile = __DIR__.'/../PictureTest/test.gif';
        $testFile = __DIR__.'/../PictureTest/test_test.gif';

        $this->getFileService()->addFileGroup(array(
            'name' => '临时目录',
            'code' => 'tmp',
            'public' => 1,
        ));

        copy($sourceFile, $testFile);
        $file = new UploadedFile(
            $testFile,
            'original.gif',
            'image/gif',
            filesize($testFile),
            UPLOAD_ERR_OK,
            true
        );

        $fileRecord = $this->getFileService()->addFile('tmp', $file);
        $data = array(
            array(
                'id' => $fileRecord['id'],
                'type' => 'small',
            ),
            array(
                'id' => $fileRecord['id'],
                'type' => 'middle',
            ),
            array(
                'id' => $fileRecord['id'],
                'type' => 'large',
            ),
        );

        $this->mockUploadService();
        $updated = $this->getOpenCourseService()->changeCoursePicture($course['id'], $data);
        $this->assertNotEmpty($updated['smallPicture']);
    }

    public function testUpdateLiveLesson()
    {
        $this->mockBiz('OpenCourse:OpenCourseLessonDao', array(
            array(
                'functionName' => 'search',
                'runTimes' => 1,
                'returnValue' => array(),
                'withParams' => array(array('courseId' => 1), array('startTime' => 'DESC'), 0, 1),
            ),
            array(
                'functionName' => 'search',
                'runTimes' => 1,
                'returnValue' => array(array('id' => 1)),
                'withParams' => array(array('courseId' => 2), array('startTime' => 'DESC'), 0, 1),
            ),
            array(
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => array('id' => 1, 'courseId' => 2, 'type' => 'liveOpen'),
            ),
            array(
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => array('id' => 1, 'type' => 'liveOpen', 'mediaId' => 1, 'courseId' => 1),
            ),
            array(
                'functionName' => 'create',
                'runTimes' => 1,
                'returnValue' => array('id' => 1, 'type' => 'live', 'mediaId' => 0, 'courseId' => 1, 'status' => 'published'),
            ),
            array(
                'functionName' => 'count',
                'runTimes' => 1,
                'returnValue' => 1,
            ),
            array(
                'functionName' => 'getLessonMaxSeqByCourseId',
                'runTimes' => 1,
                'returnValue' => 1,
            ),
        ));

        $this->mockBiz('OpenCourse:LiveCourseService', array(
            array(
                'functionName' => 'editLiveRoom',
                'runTimes' => 1,
                'returnValue' => array(),
            ),
            array(
                'functionName' => 'createLiveRoom',
                'runTimes' => 1,
                'returnValue' => array('id' => 1, 'provider' => 7),
            ),
        ));

        $this->mockBiz('OpenCourse:OpenCourseDao', array(
            array(
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => array('id' => 1, 'status' => 'published', 'type' => 'live', 'courseId' => 1),
            ),
            array(
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => array('id' => 1, 'status' => 'published', 'type' => 'live', 'courseId' => 1, 'largePicture' => '', 'middlePicture' => '', 'smallPicture' => '', 'about' => ''),
            ),
        ));

        ReflectionUtils::invokeMethod($this->getOpenCourseService(), 'updateLiveLesson', array(array('id' => 2, 'title' => 'title'), array('authUrl' => 'www.baidu.com', 'jumpUrl' => 'www.qq.com')));
        ReflectionUtils::invokeMethod($this->getOpenCourseService(), 'updateLiveLesson', array(array('id' => 1, 'title' => 'title'), array('authUrl' => 'www.baidu.com', 'jumpUrl' => 'www.qq.com', 'startTime' => time(), 'length' => 10)));
    }

    public function testUpdateCourse()
    {
        $course1 = $this->_createLiveOpenCourse();
        $updateFields = array(
            'title' => 'title2',
        );

        $lessonFields = array(
            'courseId' => $course1['id'],
            'title' => $course1['title'].'的课时',
            'type' => 'video',
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );

        $this->mockUploadService();

        $lesson = $this->getOpenCourseService()->createLesson($lessonFields);

        $updatedCourse = $this->getOpenCourseService()->updateCourse($course1['id'], $updateFields);

        $this->assertEquals($updateFields['title'], $updatedCourse['title']);
    }

    public function testDeleteCourse()
    {
        $course1 = $this->_createLiveOpenCourse();
        $this->getOpenCourseService()->deleteCourse($course1['id']);

        $course = $this->getOpenCourseService()->getCourse($course1['id']);

        $this->assertNull($course);
    }

    public function testWaveCourse()
    {
        $course1 = $this->_createLiveOpenCourse();
        $this->getOpenCourseService()->waveCourse($course1['id'], 'hitNum', 2);
        $course = $this->getOpenCourseService()->getCourse($course1['id']);

        $this->assertEquals(2, $course['hitNum']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testFavoriteCourseUnLoginException()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getOpenCourseService()->favoriteCourse(1);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testFavoriteCourseNotFoundException()
    {
        $this->getOpenCourseService()->favoriteCourse(1);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.favor_unpublished
     */
    public function testFavoriteCourseUnpublishException()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->favoriteCourse($course['id']);
    }

    public function testFavoriteCourse()
    {
        $course = $this->_createLiveOpenCourse();
        $this->getOpenCourseService()->updateCourse($course['id'], array('status' => 'published'));

        $courseFavoriteNum = $this->getOpenCourseService()->favoriteCourse($course['id']);

        $this->assertEquals(1, $courseFavoriteNum);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.duplicate_favor
     */
    public function testFavoriteCourseDuPuplicateException()
    {
        $course = $this->_createLiveOpenCourse();
        $this->getOpenCourseService()->updateCourse($course['id'], array('status' => 'published'));

        $this->getOpenCourseService()->favoriteCourse($course['id']);

        $this->getOpenCourseService()->favoriteCourse($course['id']);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.unlogin
     */
    public function testUnFavoriteCourseUnLoginException()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getOpenCourseService()->unFavoriteCourse(1);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testUnFavoriteCourseNotFoundException()
    {
        $this->getOpenCourseService()->unFavoriteCourse(1);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.cancel_unfavor
     */
    public function testUnFavoriteCourseNotFavorException()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->updateCourse($course['id'], array('status' => 'published'));

        $this->getOpenCourseService()->unFavoriteCourse($course['id']);
    }

    public function testUnFavoriteCourse()
    {
        $course = $this->_createLiveOpenCourse();
        $this->getOpenCourseService()->updateCourse($course['id'], array('status' => 'published'));

        $courseFavoriteNum = $this->getOpenCourseService()->favoriteCourse($course['id']);

        $newCourseFavoriteNum = $this->getOpenCourseService()->unFavoriteCourse($course['id']);

        $this->assertEquals(1, $courseFavoriteNum);
        $this->assertEquals(0, $newCourseFavoriteNum);
    }

    public function testGetFavoriteByUserIdAndCourseId()
    {
        $course = $this->_createLiveOpenCourse();
        $this->getOpenCourseService()->updateCourse($course['id'], array('status' => 'published'));

        $this->getOpenCourseService()->favoriteCourse($course['id']);

        $memberFavorite = $this->getOpenCourseService()->getFavoriteByUserIdAndCourseId($this->getCurrentUser()->id, $course['id'], 'openCourse');

        $this->assertEquals($course['id'], $memberFavorite['courseId']);
        $this->assertEquals('openCourse', $memberFavorite['type']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testCreateLessonParamMissException()
    {
        $lesson = array();
        $this->getOpenCourseService()->createLesson($lesson);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testCreateLessonParamError()
    {
        $lessonFields = array(
            'courseId' => 0,
            'title' => '课时1',
            'type' => 'video',
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );
        $this->mockUploadService();

        $this->getOpenCourseService()->createLesson($lessonFields);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testCreateLessonCourseNotFound()
    {
        $lessonFields = array(
            'courseId' => 1,
            'title' => '课时1',
            'type' => 'video',
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );
        $this->mockUploadService();

        $this->getOpenCourseService()->createLesson($lessonFields);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.lesson_type_invalid
     */
    public function testCreateLessonTypeInvalid()
    {
        $course = $this->_createOpenCourse();
        $lessonFields = array(
            'courseId' => $course['id'],
            'title' => $course['title'].'课时1',
            'type' => 'text',
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );
        $this->mockUploadService();

        $this->getOpenCourseService()->createLesson($lessonFields);
    }

    public function testCreateLesson()
    {
        $course = $this->_createOpenCourse();

        $lessonFields = array(
            'courseId' => $course['id'],
            'title' => $course['title'].'的课时',
            'type' => 'video',
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );
        $this->mockUploadService();

        $lesson = $this->getOpenCourseService()->createLesson($lessonFields);
        $this->assertNotEmpty($lesson);
        $this->assertEquals($lessonFields['title'], $lesson['title']);
        $this->assertEquals($lessonFields['courseId'], $lesson['courseId']);
        $this->assertEquals($lessonFields['mediaId'], $lesson['mediaId']);
    }

    public function testPublishCourse()
    {
        $course = $this->_createOpenCourse();

        $result = $this->getOpenCourseService()->publishCourse($course['id']);
        $this->assertEquals('请先添加课时并发布！', $result['message']);

        $lessonFields = array(
            'courseId' => $course['id'],
            'title' => $course['title'].'的课时',
            'type' => 'video',
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );

        $this->mockUploadService();

        $this->getOpenCourseService()->createLesson($lessonFields);
        $result = $this->getOpenCourseService()->publishCourse($course['id']);

        $this->assertEquals('published', $result['course']['status']);
    }

    public function testCloseCourse()
    {
        $course = $this->_createOpenCourse();

        $lessonFields = array(
            'courseId' => $course['id'],
            'title' => $course['title'].'的课时',
            'type' => 'video',
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );
        $this->mockUploadService();

        $this->getOpenCourseService()->createLesson($lessonFields);
        $result = $this->getOpenCourseService()->publishCourse($course['id']);

        $course = $this->getOpenCourseService()->closeCourse($course['id']);

        $this->assertEquals('closed', $course['status']);
    }

    public function testGetLessonItems()
    {
        $course = $this->_createOpenCourse();
        $lesson1 = array(
            'title' => 'openCourseLesson',
            'courseId' => $course['id'],
            'createdTime' => time(),
            'userId' => 1,
            'status' => 'published',
            'type' => 'open',
            'seq' => 2,
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );

        $this->mockUploadService();

        $lesson1 = $this->getOpenCourseService()->createLesson($lesson1);

        /*$lesson2 = array(
        'title'       => 'openCourseLesson',
        'courseId'    => $course['id'],
        'createdTime' => time(),
        'userId'      => 1,
        'status'      => 'published',
        'type'        => 'open',
        'seq'         => 1
        );
        $lesson2 = $this->getOpenCourseService()->createLesson($lesson2);*/

        $lessonsSeq = $this->getOpenCourseService()->getLessonItems($course['id']);

        $this->assertEquals('1', $lessonsSeq["lesson-{$lesson1['id']}"]['seq']);
        //$this->assertEquals('2', $lessonsSeq["lesson-{$lesson2['id']}"]['seq']);
    }

    /**
     * open_course_lesson.
     */
    public function testGetLesson()
    {
        $course = $this->_createLiveOpenCourse();
        $createLesson = $this->_createOpenLiveCourseLesson($course);
        $lesson = $this->getOpenCourseService()->getLesson($createLesson['id']);

        $this->assertEquals($createLesson['title'], $lesson['title']);
    }

    public function testGetCourseLesson()
    {
        $course = $this->_createLiveOpenCourse();
        $createLesson = $this->_createOpenLiveCourseLesson($course);
        $lesson = $this->getOpenCourseService()->getLesson($course['id'], $createLesson['id']);

        $this->assertEquals($createLesson['title'], $lesson['title']);
    }

    public function testFindLessonsByIds()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $lesson1 = $this->_createOpenLiveCourseLesson($course1);
        $lesson2 = $this->_createOpenCourseLesson($course2);

        $ids = array($lesson1['id'], $lesson2['id']);

        $lessons = $this->getOpenCourseService()->findLessonsByIds($ids);

        $this->assertEquals($lessons[0]['title'], $lesson1['title']);
        $this->assertEquals($lessons[1]['title'], $lesson2['title']);
    }

    public function testFindLessonsByCourseId()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $lesson1 = $this->_createOpenLiveCourseLesson($course1);
        $lesson2 = $this->_createOpenCourseLesson($course2);

        $lessons = $this->getOpenCourseService()->findLessonsByCourseId($course2['id']);

        $this->assertCount(1, $lessons);
        $this->assertEquals($lessons[0]['title'], $lesson2['title']);
    }

    public function testSearchLessons()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $lesson1 = $this->_createOpenLiveCourseLesson($course1);
        $lesson2 = $this->_createOpenCourseLesson($course2);

        $lessons = $this->getOpenCourseService()->searchLessons(array('type' => 'liveOpen'), array('createdTime' => 'DESC'), 0, 1);

        $this->assertCount(1, $lessons);
        $this->assertEquals($lessons[0]['title'], $lesson1['title']);
    }

    public function testSearchLessonCount()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $lesson1 = $this->_createOpenLiveCourseLesson($course1);
        $lesson2 = $this->_createOpenCourseLesson($course2);

        $count = $this->getOpenCourseService()->countLessons(array('type' => 'liveOpen'));

        $this->assertEquals(1, $count);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testUpdateLessonCourseNotFound()
    {
        $this->getOpenCourseService()->updateLesson(1, 1, array());
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found_lesson
     */
    public function testUpdateLessonLessonNotFound()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->updateLesson($course['id'], 1, array());
    }

    public function testUpdateLesson()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $time = time();
        $updateFields = array(
            'title' => 'openLiveCourseLessonUpdate',
            'type' => 'liveOpen',
            'length' => '2',
            'startTime' => $time,
            'media' => '',
            'replayStatus' => 'ungenerated',
        );

        $this->mockUploadService();
        $updateLesson = $this->getOpenCourseService()->updateLesson($lesson1['id'], $lesson1['id'], $updateFields);

        $this->assertEquals($updateFields['title'], $updateLesson['title']);
        $this->assertEquals($updateFields['type'], $updateLesson['type']);
        $this->assertEquals($updateFields['startTime'], $updateLesson['startTime']);
    }

    public function testDeleteLesson()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $this->getOpenCourseService()->deleteLesson($lesson1['id']);
        $lesson = $this->getOpenCourseService()->getLesson($lesson1['id']);

        $this->assertNull($lesson);
    }

    public function testWaveCourseLesson()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $lesson = $this->getOpenCourseService()->waveCourseLesson($lesson1['id'], 'materialNum', +1);

        $updatedLesson = $this->getOpenCourseService()->getCourseLesson($lesson1['courseId'], $lesson1['id']);
        $this->assertEquals(1, $updatedLesson['materialNum']);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found_lesson
     */
    public function testUnPublishLessonException()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->unpublishLesson($course['id'], 1);
    }

    public function testUnPublishLesson()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $publishedLesson = $this->getOpenCourseService()->publishLesson($course1['id'], $lesson1['id']);
        $this->assertEquals('published', $publishedLesson['status']);

        $unPublishedLesson = $this->getOpenCourseService()->unpublishLesson($course1['id'], $lesson1['id']);
        $this->assertEquals('unpublished', $unPublishedLesson['status']);
    }

    public function testResetLessonMediaId()
    {
        $course = $this->_createOpenCourse();
        $lesson = $this->_createOpenCourseLesson($course);
        $resultTrue = $this->getOpenCourseService()->resetLessonMediaId($lesson['id']);
        $this->assertTrue($resultTrue);
        $resultFalse = $this->getOpenCourseService()->resetLessonMediaId(2333);
        $this->assertFalse($resultFalse);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.itemids_invalid
     */
    public function testSortCourseItemsItemIdsException()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->sortCourseItems($course['id'], array(1 => '', 2 => ''));
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.itemids_invalid
     */
    public function testSortCourseItemsException()
    {
        $course = $this->_createOpenCourse();
        $lesson = $this->_createOpenCourseLesson($course);
        $this->getOpenCourseService()->sortCourseItems($course['id'], array(2 => ''));
    }

    public function testPublishLesson()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $publishedLesson = $this->getOpenCourseService()->publishLesson($course1['id'], $lesson1['id']);
        $this->assertEquals('published', $publishedLesson['status']);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found_lesson
     */
    public function testPublishLessonException()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->publishLesson($course['id'], 1);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found_lesson
     */
    public function testGenerateLessonVideoReplayLessonNotFound()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->generateLessonVideoReplay($course['id'], 2, 1);
    }

    /**
     * @expectedException \Biz\File\UploadFileException
     * @expectedExceptionMessage exception.uploadfile.file_not_found
     */
    public function testGenerateLessonVideoReplayLessonFileNotFound()
    {
        $course = $this->_createLiveOpenCourse();
        $lesson = $this->_createOpenLiveCourseLesson($course);
        $this->mockBiz('File:UploadFileService', array(array(
            'functionName' => 'getFile',
            'runTimes' => 1,
            'returnValue' => array(),
        )));

        $this->getOpenCourseService()->generateLessonVideoReplay($course['id'], $lesson['id'], 1);
    }

    public function testGenerateLessonVideoReplay()
    {
        $this->mockBiz('OpenCourse:OpenCourseLessonDao', array(
            array(
                'functionName' => 'get',
                'runTimes' => 1,
                'returnValue' => array('id' => 1, 'courseId' => 1),
            ),
            array(
                'functionName' => 'update',
                'runTimes' => 1,
                'returnValue' => array('id' => 1, 'type' => 'live'),
            ),
        ));

        $this->mockBiz('File:UploadFileService', array(array(
            'functionName' => 'getFile',
            'runTimes' => 1,
            'returnValue' => array('id' => 1, 'filename' => 'name'),
        )));

        $result = $this->getOpenCourseService()->generateLessonVideoReplay(1, 1, 1);
        $this->assertEquals(1, $result['id']);
    }

    public function testLiveLessonTimeCheck()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $result1 = $this->getOpenCourseService()->liveLessonTimeCheck($course1['id'], '', strtotime('+1 day') + 10, 540);
        $this->assertEquals('error_timeout', $result1[0]);

        $result2 = $this->getOpenCourseService()->liveLessonTimeCheck($course1['id'], '', strtotime('+1 day') - 20, 10);
        $this->assertEquals('error_occupied', $result2[0]);

        $result3 = $this->getOpenCourseService()->liveLessonTimeCheck($course1['id'], '', strtotime(date('Y-m-d').' 9:00:00'), 10);
        $this->assertEquals('success', $result3[0]);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testLiveLessonTimeCheckWithExistCourse()
    {
        $this->getOpenCourseService()->liveLessonTimeCheck(1, '', strtotime('+1 day') + 10, 540);
    }

    public function testFindFinishedLivesWithinTwoHours()
    {
        $this->mockBiz('OpenCourse:OpenCourseLessonDao', array(
            array(
                'functionName' => 'findFinishedLivesWithinTwoHours',
                'returnValue' => array(array('id' => 1, 'mediaId' => 1, 'type' => 'liveOpen', 'startTime' => time() - 3600, 'endTime' => time() - 1800)),
            ),
        ));

        $results = $this->getOpenCourseService()->findFinishedLivesWithinTwoHours();

        $this->assertEquals(1, count($results));
        $this->assertEquals('liveOpen', $results[0]['type']);
        $this->assertLessThan(7200, time() - $results[0]['endTime']);
    }

    public function testUpdateLiveStatus()
    {
        $result = $this->getOpenCourseService()->updateLiveStatus(1, 'closed');
        $this->assertEmpty($result);

        $this->mockBiz('OpenCourse:OpenCourseLessonDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'progressStatus' => 'created'),
            ),
            array(
                'functionName' => 'update',
                'returnValue' => array('id' => 1, 'progressStatus' => 'closed'),
            ),
        ));
        $result = $this->getOpenCourseService()->updateLiveStatus(1, 'closed');

        $this->assertEquals('closed', $result['progressStatus']);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     */
    public function testUpdateLiveStatusException()
    {
        $this->mockBiz('OpenCourse:OpenCourseLessonDao', array(
            array(
                'functionName' => 'get',
                'returnValue' => array('id' => 1, 'progressStatus' => 'created'),
            ),
        ));

        $result = $this->getOpenCourseService()->updateLiveStatus(1, 'created');
    }

    public function testCreateMember()
    {
        $course = $this->_createOpenCourse();
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 0,
            'nickname' => '游客',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER'),
            'org' => array('id' => 1),
        ));

        $this->getServiceKernel()->setBiz($this->getBiz());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $member = array(
            'courseId' => $course['id'],
            'userId' => 1,
            'ip' => '127.0.0.1',
            'mobile' => '12312313',
            'createdTime' => time(),
        );

        $newMember = $this->getOpenCourseService()->createMember($member);
        $this->assertEquals(0, $newMember['userId']);
    }

    /**
     * open_course_member.
     */
    public function testGetMember()
    {
        $course = $this->_createLiveOpenCourse();

        $createMember = $this->_createLoginMember($course['id']);
        $member = $this->getOpenCourseService()->getMember($createMember['id']);

        $this->assertEquals($createMember['userId'], $member['userId']);
    }

    public function testGetCourseMember()
    {
        $course1 = $this->_createOpenCourse();
        $course2 = $this->_createLiveOpenCourse();

        $courseMember1 = $this->_createLoginMember($course1['id']);

        $member1 = $this->getOpenCourseService()->getCourseMember($course1['id'], 1);

        $this->assertEquals($courseMember1['userId'], $member1['userId']);
    }

    public function testGetCourseMemberByIp()
    {
        $course = $this->_createOpenCourse();
        $member1 = $this->_createLoginMember($course['id']);

        $member = $this->getOpenCourseService()->getCourseMemberByIp($course['id'], $member1['ip']);

        $this->assertEquals($member1['ip'], $member['ip']);
    }

    public function testGetCourseMemberByMobile()
    {
        $course = $this->_createOpenCourse();
        $member1 = $this->_createLoginMember($course['id']);

        $currentUser = $this->getCurrentUser();
        $currentUser->setContext('verifiedMobile', $member1['mobile']);
        $member = $this->getOpenCourseService()->getCourseMemberByMobile($course['id'], $member1['mobile']);

        $this->assertEquals($member1['mobile'], $member['mobile']);
    }

    public function testFindMembersByCourseIds()
    {
        $course1 = $this->_createOpenCourse();
        $course2 = $this->_createLiveOpenCourse();

        $courseMember1 = $this->_createLoginMember($course1['id']);
        $courseMember2 = $this->_createGuestMember($course2['id']);

        $members = $this->getOpenCourseService()->findMembersByCourseIds(array(1));

        $this->assertCount(2, $members);
        $this->assertEquals($courseMember2['userId'], $members[0]['userId']);
        $this->assertEquals($courseMember1['userId'], $members[1]['userId']);
    }

    public function testSearchMemberCount()
    {
        $course1 = $this->_createOpenCourse();
        $course2 = $this->_createLiveOpenCourse();

        $courseMember1 = $this->_createLoginMember($course1['id']);
        $courseMember2 = $this->_createGuestMember($course2['id']);

        $this->getOpenCourseService()->updateMember($courseMember2['id'], array('mobile' => '15869165222', 'isNotified' => 1));
        $membersCount = $this->getOpenCourseService()->countMembers(array('mobile' => '15869165222'));

        $this->assertEquals(1, $membersCount);
    }

    public function testSearchMembers()
    {
        $course1 = $this->_createOpenCourse();
        $course2 = $this->_createLiveOpenCourse();

        $courseMember1 = $this->_createLoginMember($course1['id']);
        $courseMember2 = $this->_createGuestMember($course2['id']);

        $this->getOpenCourseService()->updateMember($courseMember2['id'], array('mobile' => '15869165222', 'isNotified' => 1));
        $members = $this->getOpenCourseService()->searchMembers(array('mobile' => '15869165222'), array('createdTime' => 'DESC'), 0, 1);

        $this->assertCount(1, $members);
        $this->assertEquals($courseMember2['userId'], $members[0]['userId']);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testSetCourseTeachers()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 2),
                    'withParams' => array(3),
                ),
            )
        );

        $this->mockBiz(
            'OpenCourse:OpenCourseMemberDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 1)),
                ),
                array(
                    'functionName' => 'getByUserIdAndCourseId',
                    'returnValue' => array('id' => 1),
                ),
                array(
                    'functionName' => 'create',
                    'returnValue' => array('isVisible' => 1, 'userId' => 3),
                ),
                array(
                    'functionName' => 'delete',
                    'returnValue' => array(),
                ),
            )
        );
        $this->getOpenCourseService()->setCourseTeachers(1, array(array('id' => 3)));
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_error
     */
    public function testSetCourseTeachersErrorParam()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->setCourseTeachers($course['id'], array(array('username' => 'adead')));
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testSetCourseTeachersNotFoundException()
    {
        $course = $this->_createOpenCourse();
        $this->getOpenCourseService()->setCourseTeachers($course['id'], array(array('id' => 2233)));
    }

    public function testUpdateMember()
    {
        $course = $this->_createLiveOpenCourse();

        $courseMember1 = $this->_createLoginMember($course['id']);

        $updateMember = array('role' => 'teacher');
        $member = $this->getOpenCourseService()->updateMember($courseMember1['id'], $updateMember);

        $this->assertEquals($updateMember['role'], $member['role']);
    }

    public function testDeleteMember()
    {
        $course = $this->_createLiveOpenCourse();

        $courseMember1 = $this->_createLoginMember($course['id']);
        $this->getOpenCourseService()->deleteMember($courseMember1['id']);
        $member = $this->getOpenCourseService()->getMember($courseMember1['id']);

        $this->assertNull($member);
    }

    public function testGetNextLesson()
    {
        $course = $this->_createOpenCourse();
        $lesson1 = $this->_createOpenCourseLesson($course);
        $lesson2 = $this->_createOpenCourseLesson($course);

        $lesson1 = $this->getOpenCourseService()->publishLesson($course['id'], $lesson1['id']);
        $lesson2 = $this->getOpenCourseService()->publishLesson($course['id'], $lesson2['id']);

        $nextLesson = $this->getOpenCourseService()->getNextLesson($course['id'], $lesson1['id']);

        $this->assertArrayEquals($lesson2, $nextLesson);

        $nextLesson = $this->getOpenCourseService()->getNextLesson($course['id'], $lesson2['id']);
        $this->assertEquals(empty($nextLesson), true);
    }

    /**
     * @expectedException \Biz\OpenCourse\OpenCourseException
     * @expectedExceptionMessage exception.opencourse.not_found
     */
    public function testGetNextLessonWithExistLesson()
    {
        $this->getOpenCourseService()->getNextLesson(1, 1);
    }

    public function testGetTodayOpenLiveCourseNumber()
    {
        $beginToday = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $endToday = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y')) - 1;
        $this->mockBiz(
            'OpenCourse:OpenCourseLessonDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(
                        array('id' => 2, 'courseId' => 2),
                        array('id' => 3, 'courseId' => 3),
                    ),
                    'withParams' => array(
                        array('type' => 'liveOpen', 'startTimeGreaterThan' => $beginToday, 'endTimeLessThan' => $endToday, 'status' => 'published'),
                        array(),
                        0,
                        PHP_INT_MAX,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'OpenCourse:OpenCourseMemberDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array()),
                    'withParams' => array(array('courseId' => 2, 'role' => 'teacher'), array(), 0, PHP_INT_MAX),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 2, 'userId' => 1)),
                    'withParams' => array(array('courseId' => 3, 'role' => 'teacher'), array(), 0, PHP_INT_MAX),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'OpenCourse:OpenCourseDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 2, 'title' => 'title', 'status' => 'published'),
                    'withParams' => array(3),
                ),
            )
        );

        $result = $this->getOpenCourseService()->getTodayOpenLiveCourseNumber();
        $this->assertEquals(1, $result);
    }

    public function testFindOpenLiveCourse()
    {
        $this->mockBiz(
            'OpenCourse:OpenCourseLessonDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(
                        array('id' => 2, 'courseId' => 2, 'startTime' => 6000, 'endTime' => 7000),
                        array('id' => 3, 'courseId' => 3, 'startTime' => 7000, 'endTime' => 8000),
                    ),
                    'withParams' => array(
                        array('type' => 'liveOpen', 'startTimeGreaterThan' => 5000, 'endTimeLessThan' => 10000, 'status' => 'published'),
                        array(),
                        0,
                        PHP_INT_MAX,
                    ),
                ),
            )
        );
        $this->mockBiz(
            'OpenCourse:OpenCourseMemberDao',
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
            'OpenCourse:OpenCourseDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 2, 'title' => 'title', 'status' => 'published'),
                    'withParams' => array(3),
                ),
            )
        );
        $result = $this->getOpenCourseService()->findOpenLiveCourse(
            array('startTime_GE' => 5000, 'endTime_LT' => 10000),
            2
        );

        $this->assertEquals('title', $result[0]['title']);
    }

    public function testBatchUpdateOrg()
    {
        $magic = $this->getSettingService()->set('magic', array('enable_org' => 1));
        $magic = $this->getSettingService()->get('magic');

        $org1 = $this->mookOrg($name = 'edusoho1');
        $org1 = $this->getOrgService()->createOrg($org1);

        $org2 = $this->mookOrg($name = 'edusoho2');
        $org2 = $this->getOrgService()->createOrg($org2);

        $course = array(
            'type' => 'open',
            'title' => '公开课',
            'orgCode' => $org1['orgCode'],
        );
        $course = $this->getOpenCourseService()->createCourse($course);

        $this->assertEquals($org1['id'], $course['orgId']);
        $this->assertEquals($org1['orgCode'], $course['orgCode']);

        $this->getOpenCourseService()->batchUpdateOrg($course['id'], $org2['orgCode']);

        $course = $this->getOpenCourseService()->getCourse($course['id']);

        $this->assertEquals($org2['id'], $course['orgId']);
        $this->assertEquals($org2['orgCode'], $course['orgCode']);
    }

    public function testFindCourseTeachers()
    {
        $resultEmpty = $this->getOpenCourseService()->findCourseTeachers(1);
        $this->assertEmpty($resultEmpty);

        $course = $this->_createOpenCourse();
        $teachers = $this->getOpenCourseService()->findCourseTeachers($course['id']);
        $this->assertEquals($this->getCurrentUser()->getId(), $teachers[0]['userId']);
        $this->assertEquals('teacher', $teachers[0]['role']);
    }

    public function testFilterCourseFields()
    {
        $this->mockBiz(
            'Taxonomy:TagService',
            array(
                array(
                    'functionName' => 'findTagsByNames',
                    'returnValue' => array(
                        array('id' => 1),
                    ),
                ),
            )
        );

        $result = ReflectionUtils::invokeMethod($this->getOpenCourseService(), '_filterCourseFields', array(
            array('tags' => 1, 'about' => 'about'),
        ));
        $this->assertEquals('about', $result['about']);
        $this->assertEquals(1, $result['tags'][0]);
    }

    public function testPrepareCourseConditions()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUserByNickname',
                    'returnValue' => array('id' => 1),
                ),
            )
        );
        $this->mockBiz(
            'Taxonomy:CategoryService',
            array(
                array(
                    'functionName' => 'findCategoryChildrenIds',
                    'returnValue' => array(
                        array(1, 2),
                    ),
                ),
            )
        );

        $result = ReflectionUtils::invokeMethod($this->getOpenCourseService(), '_prepareCourseConditions', array(
            array('creator' => 'creator', 'categoryId' => 1, 'nickname' => 'nickname'),
        ));
        $this->assertEquals(1, $result['userId']);
    }

    private function mookOrg($name)
    {
        $org = array();
        $org['name'] = $name;
        $org['code'] = $name;

        return $org;
    }

    private function _createLiveOpenCourse()
    {
        $course = array(
            'title' => 'liveOpenCourse',
            'type' => 'liveOpen',
            'userId' => 1,
            'createdTime' => time(),
        );

        return $this->getOpenCourseService()->createCourse($course);
    }

    private function _createOpenCourse()
    {
        $course = array(
            'title' => 'openCourse',
            'type' => 'open',
            'userId' => 1,
            'createdTime' => time(),
        );

        return $this->getOpenCourseService()->createCourse($course);
    }

    private function _createOpenLiveCourseLesson($course)
    {
        $lesson = array(
            'title' => 'openLiveCourseLesson',
            'courseId' => $course['id'],
            'createdTime' => time(),
            'userId' => 1,
            'status' => 'published',
            'type' => 'liveOpen',
            'startTime' => strtotime('+1 day'),
            'length' => 60,
        );

        return $this->getOpenCourseService()->createLesson($lesson);
    }

    private function _createOpenCourseLesson($course)
    {
        $lesson = array(
            'title' => 'openCourseLesson',
            'courseId' => $course['id'],
            'createdTime' => time(),
            'userId' => 1,
            'status' => 'published',
            'type' => 'video',
            'mediaId' => 1,
            'mediaName' => '',
            'mediaUri' => '',
            'mediaSource' => 'self',
        );

        $this->mockUploadService();

        return $this->getOpenCourseService()->createLesson($lesson);
    }

    private function _createGuestMember($courseId)
    {
        $member = array(
            'courseId' => $courseId,
            'userId' => 0,
            'ip' => '127.0.0.1',
            'mobile' => '15869165222',
            'createdTime' => time(),
        );

        return $this->getOpenCourseService()->createMember($member);
    }

    private function _createLoginMember($courseId)
    {
        $member = array(
            'courseId' => $courseId,
            'userId' => 1,
            'ip' => '127.0.0.1',
            'mobile' => '12312313',
            'createdTime' => time(),
        );

        return $this->getOpenCourseService()->createMember($member);
    }

    private function mockUploadService()
    {
        $params = array(
            array(
                'functionName' => 'getFile',
                'runTimes' => 1,
                'returnValue' => array(
                    'id' => 1,
                    'storage' => 'cloud',
                    'filename' => 'test file',
                    'fileSize' => '1024',
                    'createdUserId' => 1,
                ),
            ),
            array(
                'functionName' => 'waveUploadFile',
                'runTimes' => 1,
                'returnValue' => true,
            ),
            array(
                'functionName' => 'waveUsedCount',
                'runTimes' => 1,
                'returnValue' => true,
            ),
        );
        $this->mockBiz('File:UploadFileService', $params);
    }

    public function getOrgService()
    {
        return $this->createService('Org:OrgService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->createService('User:UserService');
    }

    /**
     * @return FileService
     */
    private function getFileService()
    {
        return $this->createService('Content:FileService');
    }
}
