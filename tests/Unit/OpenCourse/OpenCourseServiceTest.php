<?php

namespace Tests\Unit\OpenCourse;

use Biz\BaseTestCase;
use Biz\OpenCourse\Service\OpenCourseService;

class OpenCourseServiceTest extends BaseTestCase
{
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

    public function testUpdateCourse()
    {
        $course1 = $this->_createLiveOpenCourse();
        $updateFields = array('title' => 'liveOpenCourseTitle');

        $updatecCourse = $this->getOpenCourseService()->updateCourse($course1['id'], $updateFields);

        $this->assertEquals($updateFields['title'], $updatecCourse['title']);
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

    public function testFavoriteCourse()
    {
        $course = $this->_createLiveOpenCourse();
        $this->getOpenCourseService()->updateCourse($course['id'], array('status' => 'published'));

        $courseFavoriteNum = $this->getOpenCourseService()->favoriteCourse($course['id']);

        $this->assertEquals(1, $courseFavoriteNum);
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

    public function testUpdateLesson()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $updateFields = array('title' => 'openLiveCourseLessonUpdate');

        $updateLesson = $this->getOpenCourseService()->updateLesson($lesson1['id'], $lesson1['id'], $updateFields);

        $this->assertEquals($updateFields['title'], $updateLesson['title']);
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

    public function testUnpublishLesson()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $publishedLesson = $this->getOpenCourseService()->publishLesson($course1['id'], $lesson1['id']);
        $this->assertEquals('published', $publishedLesson['status']);

        $unPublishedLesson = $this->getOpenCourseService()->unpublishLesson($course1['id'], $lesson1['id']);
        $this->assertEquals('unpublished', $unPublishedLesson['status']);
    }

    public function testPublishLesson()
    {
        $course1 = $this->_createLiveOpenCourse();
        $lesson1 = $this->_createOpenLiveCourseLesson($course1);

        $publishedLesson = $this->getOpenCourseService()->publishLesson($course1['id'], $lesson1['id']);
        $this->assertEquals('published', $publishedLesson['status']);
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

    public function getCourseMemberByIp($courseId, $ip)
    {
        $member1 = $this->_createLoginMember(1);

        $member = $this->getOpenCourseService()->getCourseMemberByIp(1, $member1['ip']);

        $this->assertEquals($member1['ip'], $member['ip']);
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
}
