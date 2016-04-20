<?php
namespace Topxia\Service\OpenCourse\Tests;

use Topxia\Service\Common\BaseTestCase;

class OpenCourseServiceTest extends BaseTestCase
{
    /**
     * open_course
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

        $courses = $this->getOpenCourseService()->searchCourses(array('type' => 'liveOpen'), array('createdTime', 'DESC'), 0, 1);

        $this->assertNotEmpty($courses);
        $this->assertEquals($courses[0]['title'], $course1['title']);
    }

    public function testSearchCourseCount()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $courseCount = $this->getOpenCourseService()->searchCourseCount(array('type' => 'liveOpen'));

        $this->assertEquals(1, $courseCount);
    }

    public function testUpdateCourse()
    {
        $course1      = $this->_createLiveOpenCourse();
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

    /**
     * open_course_lesson
     */
    public function testGetLesson()
    {
        $course       = $this->_createLiveOpenCourse();
        $createLesson = $this->_createOpenLiveCourseLesson($course);
        $lesson       = $this->getOpenCourseService()->getLesson($createLesson['id']);

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

        $lessons = $this->getOpenCourseService()->searchLessons(array('type' => 'liveOpen'), array('createdTime', 'DESC'), 0, 1);

        $this->assertCount(1, $lessons);
        $this->assertEquals($lessons[0]['title'], $lesson1['title']);
    }

    public function testSearchLessonCount()
    {
        $course1 = $this->_createLiveOpenCourse();
        $course2 = $this->_createOpenCourse();

        $lesson1 = $this->_createOpenLiveCourseLesson($course1);
        $lesson2 = $this->_createOpenCourseLesson($course2);

        $count = $this->getOpenCourseService()->searchLessonCount(array('type' => 'liveOpen'));

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

    /**
     * open_course_member
     */
    public function testGetMember()
    {
        $createMember = $this->_createLoginMember();
        $member       = $this->getOpenCourseService()->getMember($createMember['id']);

        $this->assertEquals($createMember['userId'], $member['userId']);
    }

    public function testGetCourseMember()
    {
        $courseMember1 = $this->_createLoginMember();
        $courseMember2 = $this->_createGuestMember();

        $member = $this->getOpenCourseService()->getCourseMember(1, 0);

        $this->assertEquals($courseMember2['mobile'], $member['mobile']);
    }

    public function testFindMembersByCourseIds()
    {
        $courseMember1 = $this->_createLoginMember();
        $courseMember2 = $this->_createGuestMember();

        $members = $this->getOpenCourseService()->findMembersByCourseIds(array(1));

        $this->assertCount(2, $members);
        $this->assertEquals($courseMember2['userId'], $members[0]['userId']);
        $this->assertEquals($courseMember1['userId'], $members[1]['userId']);
    }

    public function testSearchMemberCount()
    {
        $courseMember1 = $this->_createLoginMember();
        $courseMember2 = $this->_createGuestMember();

        $membersCount = $this->getOpenCourseService()->searchMemberCount(array('mobile' => '15869165222'));

        $this->assertEquals(1, $membersCount);
    }

    public function testSearchMembers()
    {
        $courseMember1 = $this->_createLoginMember();
        $courseMember2 = $this->_createGuestMember();

        $members = $this->getOpenCourseService()->searchMembers(array('mobile' => '15869165222'), array('createdTime', 'DESC'), 0, 1);

        $this->assertCount(1, $members);
        $this->assertEquals($courseMember2['userId'], $members[0]['userId']);
    }

    public function testUpdateMember()
    {
        $courseMember1 = $this->_createLoginMember();

        $updateMember = array('role' => 'teacher');
        $member       = $this->getOpenCourseService()->updateMember($courseMember1['id'], $updateMember);

        $this->assertEquals($updateMember['role'], $member['role']);
    }

    public function testDeleteMember()
    {
        $courseMember1 = $this->_createLoginMember();
        $this->getOpenCourseService()->deleteMember($courseMember1['id']);
        $member = $this->getOpenCourseService()->getMember($courseMember1['id']);

        $this->assertNull($member);
    }

    private function _createLiveOpenCourse()
    {
        $course = array(
            'title'       => 'liveOpenCourse',
            'type'        => 'liveOpen',
            'userId'      => 1,
            'createdTime' => time()
        );

        return $this->getOpenCourseService()->createCourse($course);
    }

    private function _createOpenCourse()
    {
        $course = array(
            'title'       => 'openCourse',
            'type'        => 'open',
            'userId'      => 1,
            'createdTime' => time()
        );

        return $this->getOpenCourseService()->createCourse($course);
    }

    private function _createOpenLiveCourseLesson($course)
    {
        $lesson = array(
            'title'       => 'openLiveCourseLesson',
            'courseId'    => $course['id'],
            'createdTime' => time(),
            'userId'      => 1,
            'status'      => 'published',
            'type'        => 'liveOpen',
            'startTime'   => strtotime('+1 day'),
            'length'      => 60
        );

        return $this->getOpenCourseService()->createLesson($lesson);
    }

    private function _createOpenCourseLesson($course)
    {
        $lesson = array(
            'title'       => 'openCourseLesson',
            'courseId'    => $course['id'],
            'createdTime' => time(),
            'userId'      => 1,
            'status'      => 'published',
            'type'        => 'open'
        );

        return $this->getOpenCourseService()->createLesson($lesson);
    }

    private function _createGuestMember()
    {
        $member = array(
            'courseId'    => 1,
            'userId'      => 0,
            'ip'          => '127.0.0.1',
            'mobile'      => '15869165222',
            'createdTime' => time()
        );

        return $this->getOpenCourseService()->createMember($member);
    }

    private function _createLoginMember()
    {
        $member = array(
            'courseId'    => 1,
            'userId'      => 1,
            'ip'          => '127.0.0.1',
            'createdTime' => time()
        );

        return $this->getOpenCourseService()->createMember($member);
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }
}
