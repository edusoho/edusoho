<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\User\CurrentUser;
use Topxia\Service\Common\BaseTestCase;

class CourseServiceTest extends BaseTestCase
{
    //=============== Course API Test [start]===============
    public function testGetCourse()
    {
        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals($course['title'], $result['title']);
    }

    public function testFindCoursesByIds()
    {
        $course1       = array(
            'title' => 'online test course 1'
        );
        $course2       = array(
            'title' => 'online test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $ids           = array(
            $createCourse1['id'],
            $createCourse2['id']
        );
        $result        = $this->getCourseService()->findCoursesByIds($ids);
        $this->assertNotEmpty($result);
        $this->assertCount(2, $result);
        $this->assertEquals($result[1]['title'], $course1['title']);
        $this->assertEquals($result[2]['title'], $course2['title']);
    }

    public function testFindCoursesByCourseIds()
    {
        $course1       = array(
            'title' => 'online test course 1'
        );
        $course2       = array(
            'title' => 'online test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $ids           = array(
            $createCourse1['id'],
            $createCourse2['id']
        );
        $result        = $this->getCourseService()->findCoursesByCourseIds($ids, 0, 1);
        $this->assertNotEmpty($result[1]);
        $this->assertCount(1, $result);
        $this->assertEquals($result[1]['title'], $course1['title']);
    }

    public function testFindCoursesByLikeTitle()
    {
        $course_like   = array(
            'title' => 'online test course 1'
        );
        $course_unlike = array(
            'title' => 'online test course 2'
        );
        $this->getCourseService()->createCourse($course_like);
        $this->getCourseService()->createCourse($course_unlike);
        $result = $this->getCourseService()->findCoursesByLikeTitle($course_like['title']);
        $this->assertCount(1, $result);
        $this->assertEquals($result[1]['title'], $course_like['title']);
    }

    public function testFindMobileVerifiedMemberCountByCourseId()
    {
        //创建一个teacher并设置为当前用户
        $teacher     = $this->createTeacherUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($teacher);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);

        //创建一个普通用户，并设置为当前用户
        $user1       = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user1);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getUserService()->changeMobile($user1['id'], '13456520930');
        $this->getCourseService()->becomeStudent($createCourse['id'], $user1['id']);
        $count = $this->getCourseService()->findMobileVerifiedMemberCountByCourseId($createCourse['id']);
        $this->assertEquals(1, $count);
        $this->getUserService()->lockUser($user1['id']);
        $count = $this->getCourseService()->findMobileVerifiedMemberCountByCourseId($createCourse['id'], 1);
        $this->assertEquals(0, $count);
    }

    public function testFindMinStartTimeByCourseId()
    {
        $course       = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->findMinStartTimeByCourseId($createCourse['id']);
        $this->assertNotEmpty($result);
    }

    public function testFindRandomCourses()
    {
        $empty = $this->getCourseService()->findRandomCourses(array(), 10);
        $this->assertEquals(empty($empty), true);
        $this->assertEquals(count($empty), 0);
        foreach (range(0, 9) as $i) {
            $course = array(
                'title' => 'test course' . $i
            );
            $course = $this->getCourseService()->createCourse($course);
            if ($i % 2 == 0) {
                $this->getCourseService()->recommendCourse($course['id'], $i);
            }
        }
        $conditions    = array(
            'recommended' => 1
        );
        $randomCourses = $this->getCourseService()->findRandomCourses($conditions, 10);
        $this->assertEquals(count($randomCourses), 5);
    }

    public function testFindNormalCoursesByAnyTagIdsAndStatus()
    {
        $tags = array(
            'name' => 'tags1',
            'name' => 'tags2',
            'name' => 'tags3'
        );
        $this->getTagService()->addTag($tags);
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->updateCourse($course['id'], array('tagIds' => array(1)));
        $result = $this->getCourseService()->findNormalCoursesByAnyTagIdsAndStatus(array('1'), 'draft', array('Rating', 'DESC'), 0, 1);
        $this->assertNotEmpty($result);
        $this->assertEquals($result[1]['title'], $course['title']);
    }

    public function testSearchCourses()
    {
        $course1 = array(
            'title' => 'test course 1'
        );
        $course2 = array(
            'title' => 'test course 2'
        );
        $course3 = array(
            'title' => 'test course 3'
        );

        $this->getCourseService()->createCourse($course1);
        $this->getCourseService()->createCourse($course2);
        $this->getCourseService()->createCourse($course3);

        $conditions = array(
            'status' => 'draft'
        );

        $result = $this->getCourseService()->searchCourses($conditions, 'popular', 0, 5);
        $this->assertCount(3, $result);
    }

    public function testSearchCourseCount()
    {
        $course1 = array(
            'title' => 'test course 1'
        );
        $course2 = array(
            'title' => 'test course 2'
        );
        $course3 = array(
            'title' => 'test course 3'
        );

        $conditions = array(
            'status' => 'draft'
        );

        $this->getCourseService()->createCourse($course1);
        $this->getCourseService()->createCourse($course2);
        $this->getCourseService()->createCourse($course3);

        $result = $this->getCourseService()->searchCourseCount($conditions);
        $this->assertEquals(3, $result);
    }

    public function testFindCoursesCountByLessThanCreatedTime()
    {
        $course1 = array(
            'title' => 'online test course 1'
        );
        $course2 = array(
            'title' => 'online test course 2'
        );

        $this->getCourseService()->createCourse($course1);
        $this->getCourseService()->createCourse($course2);
        $endTime = strtotime(date("Y-m-d", time() + 24 * 3600));
        $result  = $this->getCourseService()->findCoursesCountByLessThanCreatedTime($endTime);
        $this->assertEquals($result, 2);
    }

    public function testAnalysisCourseSumByTime()
    {
        $course1 = array(
            'title' => 'online test course 1'
        );
        $course2 = array(
            'title' => 'online test course 2'
        );

        $this->getCourseService()->createCourse($course1);
        $this->getCourseService()->createCourse($course2);
        $endTime = strtotime(date("Y-m-d", time() + 24 * 3600));
        $result  = $this->getCourseService()->analysisCourseSumByTime($endTime);
        $this->assertEquals($result[0]['count'], 2);
    }

    public function testFindUserLearnCourses()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'], $user['id']);
        $this->getCourseService()->tryLearnCourse($createCourse['id']);
        $result = $this->getCourseService()->findUserLearnCourses($user['id'], 0, 1);
        $this->assertCount(1, $result);
    }

    public function testFindUserLearnCoursesNotInClassroom()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'], $user['id']);
        $this->getCourseService()->tryLearnCourse($createCourse['id']);
        $result = $this->getCourseService()->findUserLearnCoursesNotInClassroom($user['id'], 0, 1);
        $this->assertCount(1, $result);
    }

    public function testFindUserLearnCourseCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course1 = array(
            'title' => 'test course 1'
        );
        $course2 = array(
            'title' => 'test course 2'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse2['id']);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse1['id'], $user['id']);
        $this->getCourseService()->becomeStudent($createCourse2['id'], $user['id']);
        $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $this->getCourseService()->tryLearnCourse($createCourse2['id']);

        $result = $this->getCourseService()->findUserLearnCourseCount($user['id']);
        $this->assertEquals(2, $result);
    }

    public function testfindUserLearnCourseCountNotInClassroom()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course1 = array(
            'title' => 'test course 1'
        );
        $course2 = array(
            'title' => 'test course 2'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse2['id']);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'], $user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'], $user['id']);
        $tryLearn1  = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2  = $this->getCourseService()->tryLearnCourse($createCourse2['id']);

        $result = $this->getCourseService()->findUserLearnCourseCount($user['id']);
        $this->assertEquals(2, $result);
    }

    public function testFindUserLeaningCourses()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course1 = array(
            'title' => 'test course 1'
        );
        $course2 = array(
            'title' => 'test course 2'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse2['id']);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'], $user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'], $user['id']);
        $tryLearn1  = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2  = $this->getCourseService()->tryLearnCourse($createCourse2['id']);

        $result = $this->getCourseService()->findUserLeaningCourses($user['id'], 0, 5, array("type" => "normal"));
        $this->assertCount(2, $result);
    }

    public function testFindUserLeaningCourseCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course1 = array(
            'title' => 'test course 1'
        );
        $course2 = array(
            'title' => 'test course 2'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse2['id']);
        $user          = $this->createNormalUser();
        $currentUser   = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'], $user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'], $user['id']);
        $tryLearn1  = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2  = $this->getCourseService()->tryLearnCourse($createCourse2['id']);

        $result = $this->getCourseService()->findUserLeaningCourseCount($user['id'], array("type" => "normal"));
        $this->assertEquals(2, $result);
    }

    public function testFindUserLeanedCourseCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course1        = array(
            'title' => 'test course 1'
        );
        $course2        = array(
            'title' => 'test course 2'
        );
        $createCourse1  = $this->getCourseService()->createCourse($course1);
        $createCourse2  = $this->getCourseService()->createCourse($course2);
        $publishCourse  = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse  = $this->getCourseService()->publishCourse($createCourse2['id']);
        $lesson1        = array(
            'courseId'    => $createCourse1['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $lesson2        = array(
            'courseId'    => $createCourse2['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $createdLesson2 = $this->getCourseService()->createLesson($lesson2);

        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'], $user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'], $user['id']);
        $tryLearn1  = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2  = $this->getCourseService()->tryLearnCourse($createCourse2['id']);
        $this->getCourseService()->finishLearnLesson($createCourse1['id'], $createdLesson1['id']);
        $this->getCourseService()->finishLearnLesson($createCourse2['id'], $createdLesson2['id']);
        $result = $this->getCourseService()->findUserLeanedCourseCount($user['id']);
        $this->assertEquals(2, $result);
    }

    public function testFindUserLeanedCourses()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course1        = array(
            'title' => 'test course 1'
        );
        $course2        = array(
            'title' => 'test course 2'
        );
        $createCourse1  = $this->getCourseService()->createCourse($course1);
        $createCourse2  = $this->getCourseService()->createCourse($course2);
        $publishCourse  = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse  = $this->getCourseService()->publishCourse($createCourse2['id']);
        $lesson1        = array(
            'courseId'    => $createCourse1['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $lesson2        = array(
            'courseId'    => $createCourse2['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $createdLesson2 = $this->getCourseService()->createLesson($lesson2);

        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'], $user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'], $user['id']);
        $tryLearn1  = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2  = $this->getCourseService()->tryLearnCourse($createCourse2['id']);
        $this->getCourseService()->finishLearnLesson($createCourse1['id'], $createdLesson1['id']);
        $this->getCourseService()->finishLearnLesson($createCourse2['id'], $createdLesson2['id']);
        $result = $this->getCourseService()->findUserLeanedCourses($user['id'], 0, 5);
        $this->assertCount(2, $result);
    }

    public function testFindUserTeachCourseCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'test course 1'
        );
        $conditions   = array(
            'userId' => $user['id']
        );
        $teacher      = array(
            'id' => $user['id']
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->setCourseTeachers($createCourse['id'], array('0' => $teacher));
        $result = $this->getCourseService()->findUserTeachCourseCount($conditions, $onlyPublished = false);
        $this->assertEquals(1, $result);
    }

    public function testFindUserTeachCourses()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'test course 1'
        );
        $conditions   = array(
            'userId' => $user['id']
        );
        $teacher      = array(
            'id' => $user['id']
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->setCourseTeachers($createCourse['id'], array('0' => $teacher));
        $result = $this->getCourseService()->findUserTeachCourses($conditions, 0, 5, $onlyPublished = false);
        // print_r($result);
        $this->assertCount(1, $result);
    }

    public function testFindUserFavoritedCourseCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $this->getCourseService()->favoriteCourse($createCourse['id']);
        $result = $this->getCourseService()->findUserFavoritedCourseCount($user['id']);
        $this->assertEquals(1, $result);
    }

    public function testFindUserFavoritedCourses()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $this->getCourseService()->favoriteCourse($createCourse['id']);
        $result = $this->getCourseService()->findUserFavoritedCourses($user['id'], 0, 5);
        $this->assertCount(1, $result);
    }

    public function testCreateCourse()
    {
        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);

        $this->assertGreaterThan(0, $createCourse['id']);
        $this->assertEquals($course['title'], $createCourse['title']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateCourseTwice()
    {
        $course       = array();
        $createCourse = $this->getCourseService()->createCourse($course);
    }

    public function testUpdateCourse()
    {
        $course           = array(
            'title' => 'online test course1'
        );
        $courseUpdateData = array(
            'title'   => 'updateData',
            'orgCode' => '1.'
        );
        $createCourse     = $this->getCourseService()->createCourse($course);
        $updateCourse     = $this->getCourseService()->updateCourse($createCourse['id'], $courseUpdateData);
        $this->assertEquals('updateData', $updateCourse['title']);
        $this->assertEquals('1.', $updateCourse['orgCode']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateCourseTwice()
    {
        $courseUpdateData = array(
            'title' => 'updateData'
        );
        $createCourse     = array(
            'id' => '100');
        $updateCourse     = $this->getCourseService()->updateCourse($createCourse['id'], $courseUpdateData);
        $this->assertEquals('updateData', $updateCourse['title']);
    }

    public function testUpdateCourseCounter()
    {
        $course              = array(
            'title' => 'online test course1'
        );
        $counter             = array(
            'rating'     => '1',
            'ratingNum'  => '1',
            'lessonNum'  => '1',
            'giveCredit' => '1'
        );
        $createCourse        = $this->getCourseService()->createCourse($course);
        $updateCourseCounter = $this->getCourseService()->updateCourseCounter($createCourse['id'], $counter);
        $result              = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals(1, $result['rating']);
        $this->assertEquals(1, $result['ratingNum']);
        $this->assertEquals(1, $result['lessonNum']);
        $this->assertEquals(1, $result['giveCredit']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateCourseCounterTwice()
    {
        $course              = array(
            'title' => 'online test course1'
        );
        $counter             = array();
        $createCourse        = $this->getCourseService()->createCourse($course);
        $updateCourseCounter = $this->getCourseService()->updateCourseCounter($createCourse['id'], $counter);
    }

    public function testChangeCoursePicture()
    {
        //暂时等待
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testChangeCoursePictureTwice()
    {
        $course = array(
            'id' => '100'
        );
        $this->getCourseService()->changeCoursePicture($course['id'], '100');
    }

    public function testRecommendCourse()
    {
        $course       = array(
            'id'    => '100',
            'title' => 'test title'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->recommendCourse($createCourse['id'], 1);
        $this->assertEquals('test title', $result['title']);
    }

    public function testHitCourse()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test title'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->hitCourse($createCourse['id']);
        $result = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals(1, $result['hitNum']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testHitCourseTwice()
    {
        $course = array(
            'id' => '100'
        );
        $this->getCourseService()->hitCourse($course['id']);
    }

    public function testWaveCourse()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test title'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->waveCourse($createCourse['id'], 'hitNum', +1);
        $result = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals(1, $result['hitNum']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelRecommendCourse()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test title'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->recommendCourse($user['id'], $createCourse['id']);
        $this->assertEquals('test title', $result['title']);
        $changeResult = $this->getCourseService()->cancelRecommendCourse($user['id']);
        $this->assertEmpty($changeResult);
        $this->assertEquals($createCourse['recommended'], 0);
        $this->assertEquals($createCourse['recommendedTime'], 0);
        $this->assertEquals($createCourse['recommendedSeq'], 0);
    }

    public function testAnalysisCourseDataByTime()
    {
        $course = array(
            'title' => 'test 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $startTime    = strtotime(date("Y-m-d", time() - 24 * 3600));
        $endTime      = strtotime(date("Y-m-d", time() + 24 * 3600));
        $result       = $this->getCourseService()->analysisCourseDataByTime($startTime, $endTime);
        $this->assertEquals('1', $result[0]['count']);
    }

    public function testFindLearnedCoursesByCourseIdAndUserId()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course1        = array(
            'title' => 'test course 1'
        );
        $course2        = array(
            'title' => 'test course 2'
        );
        $createCourse1  = $this->getCourseService()->createCourse($course1);
        $createCourse2  = $this->getCourseService()->createCourse($course2);
        $publishCourse  = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse  = $this->getCourseService()->publishCourse($createCourse2['id']);
        $lesson1        = array(
            'courseId'    => $createCourse1['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $lesson2        = array(
            'courseId'    => $createCourse2['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $createdLesson2 = $this->getCourseService()->createLesson($lesson2);

        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'], $user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'], $user['id']);
        $tryLearn1  = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2  = $this->getCourseService()->tryLearnCourse($createCourse2['id']);
        $this->getCourseService()->finishLearnLesson($createCourse1['id'], $createdLesson1['id']);
        $result = $this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($createCourse1['id'], $user['id']);
        // print_r($result);
        $this->assertCount(1, $result);
    }

    public function testUploadCourseFile()
    {
        //暂时等待
    }

    public function testSetCoursePrice()
    {
        $course       = array(
            'title' => 'test course'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $setPrice     = $this->getCourseService()->setCoursePrice($createCourse['id'], 'default', 100);
        $this->assertEquals(100, $setPrice['originPrice']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSetCoursePriceTwice()
    {
        $course       = array(
            'title' => 'test course'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $setPrice     = $this->getCourseService()->setCoursePrice($createCourse['id'], 'hahaha', 100);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSetCoursePriceThird()
    {
        $course   = array(
            'id' => '100'
        );
        $setPrice = $this->getCourseService()->setCoursePrice($course['id'], 'coin', 100);
    }

    public function testSetCoursesPriceWithDiscount()
    {
        //需要插件支持
    }

    public function testRevertCoursesPriceWithDiscount()
    {
        //需要插件支持
    }

//=============== Course API Test [end] ================

    //================= 课程CRUD [start] ==================

    public function testDeleteCourse()
    {
        $user          = $this->getCurrentUser();
        $course1       = array(
            'title' => 'test course 1'
        );
        $course2       = array(
            'title' => 'test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);

        $publishCourse1 = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse2 = $this->getCourseService()->publishCourse($createCourse2['id']);

        $conditions           = array(
            'userId' => $user['id']
        );
        $findUserTeachCourses = $this->getCourseService()->findUserTeachCourses($conditions, 0, 5);
        $this->assertCount(2, $findUserTeachCourses);
        $this->getCourseService()->deleteCourse($createCourse1['id']);
        $findUserTeachCourses = $this->getCourseService()->findUserTeachCourses($conditions, 0, 5);
        $this->assertCount(1, $findUserTeachCourses);
    }

    public function testPublishCourse()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course1       = array(
            'title' => 'test course 1'
        );
        $course2       = array(
            'title' => 'test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);

        $publishCourse1 = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse2 = $this->getCourseService()->publishCourse($createCourse2['id']);
        $result1        = $this->getCourseService()->getCourse($createCourse1['id']);
        $result2        = $this->getCourseService()->getCourse($createCourse2['id']);
        $this->assertEquals($result1['status'], 'published');
        $this->assertEquals($result2['status'], 'published');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testPublishCourseTwice()
    {
        $createCourse1 = array(
            'id' => '100'
        );
        $this->getCourseService()->publishCourse($createCourse1['id'], 'classroom');
    }

    public function testCloseCourse()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course1       = array(
            'title' => 'test course 1'
        );
        $course2       = array(
            'title' => 'test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);

        $publishCourse1 = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse2 = $this->getCourseService()->publishCourse($createCourse2['id']);
        $this->getCourseService()->closeCourse($createCourse1['id']);
        $result1 = $this->getCourseService()->getCourse($createCourse1['id']);
        $result2 = $this->getCourseService()->getCourse($createCourse2['id']);
        $this->assertEquals('closed', $result1['status']);
        $this->assertEquals('published', $result2['status']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCloseCourseTwice()
    {
        $createCourse1 = array(
            'id' => '100'
        );
        $this->getCourseService()->closeCourse($createCourse1['id'], 'classroom');
    }

//================= 课程CRUD [end] ===================

    //================= Lesson API [start] ==================
    public function testGetLesson()
    {
        $course       = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $lesson       = $this->getCourseService()->getLesson($createLesson['id']);
        $this->assertNotNull($lesson);
    }

    public function testFindLessonsByIds()
    {
        $course        = array(
            'title' => 'online test course1'
        );
        $createCourse  = $this->getCourseService()->createCourse($course);
        $lesson        = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);

        $ids    = array(
            $createLesson1['id'],
            $createLesson2['id']
        );
        $result = $this->getCourseService()->findLessonsByIds($ids);
        $this->assertCount(2, $result);
    }

    public function testGetCourseLesson()
    {
        $course        = array(
            'title' => 'online test course1'
        );
        $createCourse  = $this->getCourseService()->createCourse($course);
        $lesson        = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);
        $result        = $this->getCourseService()->getCourseLesson($createCourse['id'], $createLesson1['id']);
        $this->assertEquals($result['id'], $createLesson1['id']);
    }

    public function testFindCourseDraft()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft        = array(
            'userId'   => $user['id'],
            'title'    => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $result = $this->getCourseService()->findCourseDraft($createCourse['id'], $createLesson['id'], $user['id']);
        $this->assertEquals($result['id'], $createCourse['id']);
    }

    public function testGetCourseLessons()
    {
        $course        = array(
            'title' => 'online test course1'
        );
        $createCourse  = $this->getCourseService()->createCourse($course);
        $lesson        = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);
        $result        = $this->getCourseService()->getCourseLessons($createCourse['id']);
        $this->assertCount(2, $result);
    }

    public function testDeleteCourseDrafts()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft        = array(
            'userId'   => $user['id'],
            'title'    => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $result1 = $this->getCourseService()->findCourseDraft($createCourse['id'], $createLesson['id'], $user['id']);
        $this->assertEquals('test course 1', $result1['title']);
        $this->getCourseService()->deleteCourseDrafts($createCourse['id'], $createLesson['id'], $user['id']);
        $result2 = $this->getCourseService()->findCourseDraft($createCourse['id'], $createLesson['id'], $user['id']);
        $this->assertEmpty($result2);
    }

    public function testFindLessonsByTypeAndMediaId()
    {
        //wait it know
    }

    public function testSearchLessons()
    {
        $course        = array(
            'title' => 'online test course1'
        );
        $createCourse  = $this->getCourseService()->createCourse($course);
        $lesson        = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);
        $conditions    = array(
            'courseId' => $createCourse['id']
        );
        $result        = $this->getCourseService()->searchLessons($conditions, array('createdTime', 'ASC'), 0, 5);
        $this->assertCount(2, $result);
    }

    public function testSearchLessonCount()
    {
        $course        = array(
            'title' => 'online test course1'
        );
        $createCourse  = $this->getCourseService()->createCourse($course);
        $lesson        = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);
        $conditions    = array(
            'courseId' => $createCourse['id']
        );
        $result        = $this->getCourseService()->searchLessonCount($conditions);
        $this->assertEquals(2, $result);
    }

    public function testCreateLesson()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $lesson        = array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);

        $this->assertGreaterThan(0, $createdLesson['id']);
        $this->assertEquals($lesson['courseId'], $createdLesson['courseId']);
        $this->assertEquals($lesson['title'], $createdLesson['title']);
        $this->assertEquals($lesson['content'], $createdLesson['content']);
        $this->assertEquals(1, $createdLesson['number']);
        $this->assertEquals(1, $createdLesson['seq']);

        $lesson        = array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 2',
            'content'  => 'test lesson content 2',
            'type'     => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);

        $this->assertEquals(2, $createdLesson['number']);
        $this->assertEquals(2, $createdLesson['seq']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateLessonTwice()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $lesson        = array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateLessonThird()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $lesson        = array(
            'courseId' => null,
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateLessonForth()
    {
        $course        = array(
            'id' => '100'
        );
        $lesson        = array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateLessonFifth()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $lesson        = array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'hhhh'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);
    }

    public function testCreateLessonByFileId()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $fakeFile = array(
            'id' => 1,
            'filename' => 'fake video',
            'type' => 'video',
            'length' => 100,
            'fileSize' => 1024
        );
        $this->mock('File.UploadFileService', array(
            array('functionName' => 'getFile', 'runTimes' => 2, 'returnValue' => $fakeFile),
            array('functionName' => 'waveUploadFile', 'runTimes' => 1, 'returnValue' => array())
        ));

        $lesson = $this->getCourseService()->createLessonByFileId($course['id'], $fakeFile['id']);

        $this->assertEquals($fakeFile['id'], $lesson['mediaId']);
        $this->assertEquals($fakeFile['filename'], $lesson['title']);
        $this->assertEquals($fakeFile['length'], $lesson['length']);
    }
    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCreateLessonByFileIdWithFileNotExist()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $fakeFile = array(
            'id' => 996,
            'filename' => 'fake video',
            'type' => 'video',
            'length' => 100,
            'fileSize' => 1024
        );

        $this->getCourseService()->createLessonByFileId($course['id'], $fakeFile['id']);
    }

    public function testGetCourseDraft()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft        = array(
            'userId'   => $user['id'],
            'title'    => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $result = $this->getCourseService()->getCourseDraft($createCourse['id']);
        $this->assertEquals('test course 1', $result['title']);
    }

    public function testCreateCourseDraft()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft        = array(
            'userId'   => $user['id'],
            'title'    => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $result = $this->getCourseService()->getCourseDraft($createCourse['id']);
        $this->assertEquals(1, $result['id']);
    }

    public function testUpdateLesson()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $this->assertEquals($createLesson['summary'], '');
        $newLesson = array(
            'chapterId' => 0,
            'summary'   => 'addSummary'
        );
        $result    = $this->getCourseService()->updateLesson($createCourse['id'], $createLesson['id'], $newLesson);
        $this->assertEquals($result['summary'], 'addSummary');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateLessonTwice()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'id' => '100'
        );
        $lesson       = array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $newLesson    = array(
            'chapterId' => 0,
            'summary'   => 'addSummary'
        );
        $this->getCourseService()->updateLesson($course['id'], $createLesson['id'], $newLesson);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateLessonThird()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $createLesson = array(
            'id' => '100');
        $newLesson    = array(
            'chapterId' => 0,
            'summary'   => 'addSummary'
        );
        $this->getCourseService()->updateLesson($createCourse['id'], $createLesson['id'], $newLesson);
    }

    public function testUpdateCourseDraft()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft        = array(
            'userId'   => $user['id'],
            'title'    => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $newDraft = array(
            'title' => 'newDraft'
        );
        $this->getCourseService()->updateCourseDraft($createCourse['id'], $createLesson['id'], $user['id'], $newDraft);
        $result = $this->getCourseService()->getCourseDraft($createCourse['id']);
        $this->assertEquals($result['title'], 'newDraft');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateCourseDraftTwice()
    {
        $newDraft = array(
            'title' => 'newDraft'
        );
        $this->getCourseService()->updateCourseDraft('100', '100', '100', $newDraft);
    }

    public function testDeleteLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson1 = $this->getCourseService()->createLesson(array(
            'courseId'    => $course['id'],
            'title'       => 'test lesson 1',
            'content'     => 'test lesson content 1',
            'type'        => 'text',
            'chapterId'   => 0,
            'free'        => 0,
            'number'      => '1',
            'summary'     => '',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()

        ));

        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId'    => $course['id'],
            'title'       => 'test lesson 2',
            'content'     => 'test lesson content 2',
            'type'        => 'text',
            'chapterId'   => 0,
            'free'        => 0,
            'number'      => '1',
            'summary'     => '',
            'seq'         => '1',
            'parentId'    => 1,
            'userId'      => 1,
            'createdTime' => time()
        ));

        $lesson3 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 3',
            'content'  => 'test lesson content 3',
            'type'     => 'text'
        ));

        $this->getCourseService()->deleteLesson($course['id'], $lesson1['id']);

        $this->assertEmpty($this->getCourseService()->getCourseLesson($course['id'], $lesson1['id']));

// @FIXME

// $number = 1;

// $lessons = $this->getCourseService()->getCourseLessons($course['id']);

// foreach ($lessons as $lesson) {

//     $this->assertEquals($number, $lesson['number']);

//     $number ++;
        // }
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testDeleteLessonTwice()
    {
        $course  = array('id' => '100');
        $lesson1 = array('id' => '100');
        $this->getCourseService()->deleteLesson($course['id'], $lesson1['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testDeleteLessonThrid()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson1 = array('id' => '100');
        $this->getCourseService()->deleteLesson($course['id'], $lesson1['id']);
    }

    public function testPublishLesson()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse  = $this->getCourseService()->createCourse($course);
        $lesson        = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson  = $this->getCourseService()->createLesson($lesson);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson = $this->getCourseService()->publishLesson($createCourse['id'], $createLesson['id']);
        $result        = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals($result['status'], 'published');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testPublishLessonTwice()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text',
            'id'        => '100'
        );
        // $createLesson = $this->getCourseService()->createLesson($lesson);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson = $this->getCourseService()->publishLesson($createCourse['id'], $lesson['id']);
    }

    public function testUnpublishLesson()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse  = $this->getCourseService()->createCourse($course);
        $lesson        = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson  = $this->getCourseService()->createLesson($lesson);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson = $this->getCourseService()->publishLesson($createLesson['id'], $createLesson['id']);
        $result        = $this->getCourseService()->getCourseLesson($createCourse['id'], $createLesson['id']);
        $this->assertEquals($result['status'], 'published');
        $unPublishLesson = $this->getCourseService()->unpublishLesson($createLesson['id'], $createLesson['id']);
        $result          = $this->getCourseService()->getCourseLesson($createCourse['id'], $createLesson['id']);
        $this->assertEquals($result['status'], 'unpublished');
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnpublishLessonTwice()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse    = $this->getCourseService()->createCourse($course);
        $lesson          = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson    = $this->getCourseService()->createLesson($lesson);
        $publishCourse   = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson   = $this->getCourseService()->publishLesson($createLesson['id'], $createLesson['id']);
        $unPublishLesson = $this->getCourseService()->unpublishLesson($createLesson['id'], '100');
    }

    public function testGetNextLessonNumber()
    {
        //此方法已经在createlesson测试完毕，无需再测试
    }

    public function testLiveLessonTimeCheck()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse        = $this->getCourseService()->createCourse($course);
        $lesson              = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'live',
            'startTime' => time() + 900,
            'length'    => ''
        );
        $start               = strtotime(date("Y-m-d", time()));
        $createLesson        = $this->getCourseService()->createLesson($lesson);
        $publishCourse       = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson       = $this->getCourseService()->publishLesson($createLesson['id'], $createLesson['id']);
        $liveLessonTimeCheck = $this->getCourseService()->liveLessonTimeCheck($createCourse['id'], $createLesson['id'], $start, 10000);
        $this->assertEquals('error_timeout', $liveLessonTimeCheck[0]);
        $liveLessonTimeCheck = $this->getCourseService()->liveLessonTimeCheck($createCourse['id'], $createLesson['id'], $start, 100);
        $this->assertEquals('success', $liveLessonTimeCheck[0]);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testLiveLessonTimeCheckTwice()
    {
        $this->getCourseService()->liveLessonTimeCheck('100', '100', '100', 10000);
    }

    public function testCalculateLiveCourseLeftCapacityInTimeRange()
    {
        //waiting ti know
    }

    public function testCanLearnLesson()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text',
            'startTime' => '',
            'length'    => ''
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'], $user['id']);
        $result = $this->getCourseService()->canLearnLesson($createCourse['id'], $createLesson['id']);
        $this->assertEquals('yes', $result['status']);
    }

    /**
     * @expectedException Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testCanLearnLessonTwice()
    {
        $this->getCourseService()->canLearnLesson('100', '100');
    }

    public function testStartLearnLesson()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->startLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'], $course['id'], $lesson['id']);
        $this->assertEquals('learning', $status);
    }

    public function testCreateLessonView()
    {
    }

    public function testFinishLearnLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'], $course['id'], $lesson['id']);
        $this->assertEquals('finished', $status);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFinishLearnLessonTwice()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $this->getCourseService()->finishLearnLesson($course['id'], '100');
    }

    public function testFindLatestFinishedLearns()
    {
        $result = $this->getCourseService()->findLatestFinishedLearns(0, 1);
        $this->assertEquals(array(), $result);
    }

    public function testCancelLearnLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $this->getCourseService()->cancelLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'], $course['id'], $lesson['id']);
        $this->assertEquals('learning', $status);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelLearnLessonTwice()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $user   = $this->getCurrentUser();
        $this->getCourseService()->cancelLearnLesson($course['id'], $lesson['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelLearnLessonThird()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $this->getCourseService()->cancelLearnLesson($course['id'], '100');
    }

    public function testGetUserLearnLessonStatus()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();
        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $result = $this->getCourseService()->getUserLearnLessonStatus($user['id'], $course['id'], $lesson['id']);
        $this->assertEquals('finished', $result);
    }

    public function testGetUserLearnLessonStatuses()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();
        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $result = $this->getCourseService()->getUserLearnLessonStatuses($user['id'], $course['id'], $lesson['id']);
        $this->assertNotEmpty($result);
    }

    public function testFindUserLearnedLessons()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $userLearns = $this->getCourseService()->findUserLearnedLessons($user['id'], $course['id']);
        $this->assertEquals(1, count($userLearns));
    }

    public function testGetUserNextLearnLesson()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse  = $this->getCourseService()->createCourse($course);
        $lesson        = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson  = $this->getCourseService()->createLesson($lesson);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson = $this->getCourseService()->publishLesson($createLesson['id'], $createLesson['id']);
        $result        = $this->getCourseService()->getUserNextLearnLesson($user['id'], $createCourse['id']);
        $this->assertEquals('1', $result['id']);
    }

    public function testSearchLearnCount()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson  = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 1,
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $conditions = array('status' => 'finished');
        $result     = $this->getCourseService()->searchLearnCount($conditions);
        $this->assertEquals($result, '1');
    }

    public function testSearchLearns()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson  = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 1,
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $conditions = array('status' => 'finished');
        $result     = $this->getCourseService()->searchLearns($conditions, array("finishedTime", "DESC"), 0, 100);
        $array      = $result[0];
        $this->assertEquals($array['id'], '1');
    }

    public function testAnalysisLessonDataByTime()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson  = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 1,
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();

        $startTime = strtotime(date("Y-m-d", time() - 24 * 3600));
        $endTime   = strtotime(date("Y-m-d", time() + 24 * 3600));
        $result    = $this->getCourseService()->analysisLessonDataByTime($startTime, $endTime);
        $this->assertEquals('2', $result[0]['count']);
    }

    public function testAnalysisLessonFinishedDataByTime()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson  = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 1,
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user = $this->getCurrentUser();
        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $this->getCourseService()->finishLearnLesson($course['id'], $lesson2['id']);
        $startTime = strtotime(date("Y-m-d", time() - 24 * 3600));
        $endTime   = strtotime(date("Y-m-d", time() + 24 * 3600));
        $result    = $this->getCourseService()->analysisLessonFinishedDataByTime($startTime, $endTime);
        $this->assertEquals('2', $result[0]['count']);
    }

    public function testSearchAnalysisLessonViewCount()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson  = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 1,
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $user      = $this->getCurrentUser();
        $startTime = strtotime(date("Y-m-d", time() - 24 * 3600));
        $endTime   = strtotime(date("Y-m-d", time() + 24 * 3600));
        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $this->getCourseService()->finishLearnLesson($course['id'], $lesson2['id']);
        $result = $this->getCourseService()->searchAnalysisLessonViewCount(array($startTime, $endTime));
        $this->assertEquals('0', $result);
    }

    public function testGetAnalysisLessonMinTime()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson  = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 1,
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $user    = $this->getCurrentUser();
        $result  = $this->getCourseService()->getAnalysisLessonMinTime('all');
        $this->assertNull($result);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testGetAnalysisLessonMinTimeTwice()
    {
        $result = $this->getCourseService()->getAnalysisLessonMinTime('xxx');
    }

    public function testSearchAnalysisLessonView()
    {
    }

    public function testAnalysisLessonViewDataByTime()
    {
    }

    public function testWaveLearningTime()
    {
        $user   = $this->getCurrentUser();
        $time   = time();
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $result = $this->getCourseService()->waveLearningTime($user['id'], $lesson['id'], $time);
        $this->assertNull($result);
    }

    public function testFindLearnsCountByLessonId()
    {
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson       = array(
            'courseId'  => $createCourse['id'],
            'chapterId' => 0,
            'free'      => 0,
            'title'     => 'test' + rand(),
            'summary'   => '',
            'type'      => 'text'
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $this->getCourseService()->findLearnsCountByLessonId($createLesson['id']);
    }

    public function testWaveWatchingTime()
    {
    }

    public function testSearchLearnTime()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => 0,
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $user   = $this->getCurrentUser();
        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $this->getCourseService()->searchLearnTime(array('status' => 'finished', 'lessonId' => $lesson['id']));
    }

    public function testSearchWatchTime()
    {
    }

    public function testCheckWatchNum()
    {
    }

    public function testWaveWatchNum()
    {
    }

//=============== Lesson API [end] ===================

//=============== Chapter API[start] ==================

    public function testGetChapter()
    {
        $course          = array(
            'title' => 'test course 1'
        );
        $createCourse    = $this->getCourseService()->createCourse($course);
        $chapter1        = array('courseId' => $createCourse['id'], 'title' => 'chapter 1', 'type' => 'chapter', 'number' => '1', 'seq' => '1');
        $createdChapter1 = $this->getCourseService()->createChapter($chapter1);
        $result          = $this->getCourseService()->getChapter($createCourse['id'], $createdChapter1['id']);
        $this->assertEquals('chapter 1', $result['title']);
    }

    public function testGetCourseChapters()
    {
        $course          = array(
            'title' => 'test course 1'
        );
        $createCourse    = $this->getCourseService()->createCourse($course);
        $chapter1        = array('courseId' => $createCourse['id'], 'title' => 'chapter 1', 'type' => 'chapter', 'number' => '1', 'seq' => '1');
        $createdChapter1 = $this->getCourseService()->createChapter($chapter1);
        $result          = $this->getCourseService()->getCourseChapters($createCourse['id']);
        $this->assertNotNull($result);
    }

    public function testCreateChapter()
    {
        $chapter1 = array('courseId' => 1, 'title' => 'chapter 1', 'type' => 'chapter', 'number' => '1', 'seq' => '1');
        $chapter2 = array('courseId' => 1, 'title' => 'chapter 2', 'type' => 'chapter', 'number' => '1', 'seq' => '1');
        $chapter3 = array('courseId' => 1, 'title' => 'chapter 3', 'type' => 'chapter', 'number' => '1', 'seq' => '1');

        $createdChapter1 = $this->getCourseService()->createChapter($chapter1);

        $this->assertTrue(is_array($createdChapter1));
        $this->assertEquals($chapter1['courseId'], $createdChapter1['courseId']);
        $this->assertEquals($chapter1['title'], $createdChapter1['title']);
        $this->assertEquals(1, $createdChapter1['number']);
        $this->assertEquals(1, $createdChapter1['seq']);

        $createdChapter2 = $this->getCourseService()->createChapter($chapter2);
        $this->assertEquals(2, $createdChapter2['number']);
        $this->assertEquals(2, $createdChapter2['seq']);

        $createdChapter3 = $this->getCourseService()->createChapter($chapter3);
        $this->assertEquals(3, $createdChapter3['number']);
        $this->assertEquals(3, $createdChapter3['seq']);
    }

    public function testUpdateChapter()
    {
        $course          = array(
            'title' => 'test course 1'
        );
        $createCourse    = $this->getCourseService()->createCourse($course);
        $chapter1        = array('courseId' => $createCourse['id'], 'title' => 'chapter 1', 'type' => 'chapter', 'number' => '1', 'seq' => '1', 'createdTime' => time());
        $createdChapter1 = $this->getCourseService()->createChapter($chapter1);
        $chapter         = $this->getCourseService()->updateChapter($createCourse['id'], 1, array('title' => 'chapter edit'));
        $this->assertEquals('chapter edit', $chapter['title']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUpdateChapterTwice()
    {
        $course          = array(
            'title' => 'test course 1'
        );
        $createCourse    = $this->getCourseService()->createCourse($course);
        $chapter1        = array('courseId' => $createCourse['id'], 'title' => 'chapter 1', 'type' => 'chapter', 'number' => '1', 'seq' => '1', 'createdTime' => time());
        $createdChapter1 = $this->getCourseService()->createChapter($chapter1);
        $chapter         = $this->getCourseService()->updateChapter($createCourse['id'], '100', array('title' => 'chapter edit'));
        $this->assertEquals('chapter edit', $chapter['title']);
    }

    public function testDeleteChapter()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $chapter1 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 1',
            'type'     => 'chapter',
            'number'   => '1',
            'seq'      => '1'
        ));

        $lesson1 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter1['id'],
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter1['id'],
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 2',
            'type'      => 'text'
        ));

        $chapter2 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 2',
            'type'     => 'chapter'
        ));

        $lesson3 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter2['id'],
            'title'     => 'test lesson 3',
            'content'   => 'test lesson content 3',
            'type'      => 'text'
        ));

        $chapter3 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 3',
            'type'     => 'chapter'
        ));

        $this->getCourseService()->deleteChapter($course['id'], $chapter2['id']);
        $this->assertNull($this->getCourseService()->getChapter($course['id'], $chapter2['id']));

        $lesson = $this->getCourseService()->getCourseLesson($course['id'], $lesson3['id']);
        $this->assertEquals($chapter1['id'], $lesson['chapterId']);

// @FIXME

// $number = 1;

// $chapters = $this->getCourseService()->getCourseChapters($course['id']);

// foreach ($chapters as $chapter) {

//     $this->assertEquals($number, $chapter['number']);

//     $number ++;
        // }
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testDeleteChapterTwice()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $chapter1 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 1',
            'type'     => 'chapter',
            'number'   => '1',
            'seq'      => '1'
        ));

        $lesson1 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter1['id'],
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $this->getCourseService()->deleteChapter($course['id'], '100');
    }

    public function testGetNextChapterNumber()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $chapter1 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 1',
            'type'     => 'chapter',
            'number'   => '1',
            'seq'      => '1'
        ));

        $lesson1 = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter1['id'],
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $result  = $this->getCourseService()->getNextChapterNumber($course['id']);
        $this->assertEquals('2', $result);
    }

//================ Chapter API[end] ==================

//===========获得课程的目录项[start] =======================
    public function testGetCourseItems()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $items  = $this->createCourseItems($course);

        $this->assertEquals(5, count($items));
        $seq = 1;

        foreach ($items as $item) {
            $this->assertEquals($seq, $item['seq']);
            $seq++;
        }
    }

    public function testSortCourseItems()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $items   = $this->createCourseItems($course);
        $itemIds = array_keys($items);
        shuffle($itemIds);

        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);

        $items = $this->getCourseService()->getCourseItems($course['id']);

        $seq = 1;

        foreach ($items as $itemId => $item) {
            $this->assertEquals(array_shift($itemIds), $itemId);
            $this->assertEquals($seq, $item['seq']);
            $seq++;
        }
    }

//===========获得课程的目录项[end] =======================

    //============Member API[start] ===============

    public function testSearchMembers()
    {
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $conditions   = array('role' => 'student');
        $result       = $this->getCourseService()->searchMembers($conditions, array('createdTime', 'DESC'), 0, 100);
        $this->assertEmpty($result);
    }

    public function testSearchMember()
    {
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $conditions   = array('role' => 'student');
        $result       = $this->getCourseService()->searchMember($conditions, array('createdTime', 'DESC'), 0, 100);
        $this->assertEmpty($result);
    }

    public function testCountMembersByStartTimeAndEndTime()
    {
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $conditions   = array('role' => 'student');
        $startTime    = strtotime(date("Y-m-d", time() - 24 * 3600));
        $endTime      = strtotime(date("Y-m-d", time() + 24 * 3600));
        $result       = $this->getCourseService()->countMembersByStartTimeAndEndTime($startTime, $endTime);

        $this->assertNotNull($result);
    }

    public function testSearchMemberCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course); //????
        $this->getCourseService()->publishCourse($createCourse['id']);
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $member = $this->getCourseService()->becomeStudent($createCourse['id'], $currentUser['id']);
        $this->assertNotEmpty($member);
    }

    public function testFindWillOverdueCourses()
    {
    }

    public function testGetCourseMember()
    {
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);

        $result = $this->getCourseService()->getCourseMember($createCourse['id'], $normalUser['id']);
        $this->assertEquals($result['id'], '1');
    }

    public function testSearchMemberIds()
    {
        $course = array(
            'title' => 'test course 1'
        );

        $user = $this->getCurrentUser();

        $course2 = array(
            'title' => 'test course 2'
        );

        $createCourse  = $this->getCourseService()->createCourse($course);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $conditions    = array('unique' => true, 'role' => 'teacher');
        $result        = $this->getCourseService()->searchMemberIds($conditions, 'latest', 0, 10);

        $this->assertEquals($result[0]['userId'], $user['id']);
        $this->assertEquals($result[0]['userId'], $this->getCurrentUser()->id);
        $this->assertArrayEquals($result, array(array('userId' => $this->getCurrentUser()->id)));
    }

    public function testUpdateCourseMember()
    {
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
    }

    public function testIsMemberNonExpired()
    {
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course                 = array(
            'title' => 'test course 1'
        );
        $createCourse           = $this->getCourseService()->createCourse($course);
        $normalUser['deadline'] = time();
        $result                 = $this->getCourseService()->isMemberNonExpired($createCourse, $normalUser);
        $this->assertFalse($result);
    }

    public function testCanTakeCourse()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->canTakeCourse($createCourse['id']);
        $this->assertTrue($result);
    }

    public function testFindCourseStudents()
    {
        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->findCourseStudents($createCourse['id'], 0, 100);
        $this->assertEquals(array(), $result);
    }

    public function testFindCourseStudentsByCourseIds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course); //????
        $this->getCourseService()->publishCourse($createCourse['id']);
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'], $currentUser['id']);

        $result = $this->getCourseService()->findCourseStudentsByCourseIds(array(1));
        $this->assertEquals($result[0]['id'], '1');
    }

    public function testGetCourseStudentCount()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'], $currentUser['id']);
        $count = $this->getCourseService()->getCourseStudentCount($createCourse['id']);
        $this->assertEquals('1', $count);
    }

    public function testFindCourseTeachers()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $result = $this->getCourseService()->findCourseTeachers($createCourse['id']);
        $this->assertEquals($result[0]['id'], 1);
    }

    public function testIsCourseTeacher()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $result = $this->getCourseService()->isCourseTeacher($createCourse['id'], $currentUser['id']);
        $this->assertTrue($result);
    }

    public function testIsCourseStudent()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $result = $this->getCourseService()->isCourseStudent($createCourse['id'], $currentUser['id']);
        $this->assertFalse($result);
    }

    public function testSetCourseTeachers()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);

        $teacher     = $this->createTeacherUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($teacher);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getCourseService()->setCourseTeachers($createCourse['id'], array(array('id' => $teacher['id'], 'isVisible' => 1)));
        $result = $this->getCourseService()->isCourseTeacher($createCourse['id'], $teacher['id']);
        $this->assertTrue($result);
    }

    public function testCancelTeacherInAllCourses()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);

        $teacher     = $this->createTeacherUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($teacher);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getCourseService()->setCourseTeachers($createCourse['id'], array(array('id' => $teacher['id'], 'isVisible' => 1)));
        $this->getCourseService()->cancelTeacherInAllCourses($teacher['id']);
        $result = $this->getCourseService()->isCourseTeacher($createCourse['id'], $teacher['id']);
        $this->assertFalse($result);
    }

    public function testRemarkStudent()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);

        $result = $this->getCourseService()->remarkStudent($createCourse['id'], $user['id'], 'remark');
        $this->assertEquals($result['id'], '1');
    }

//============Member API[end] ===============

    //============成为学员，即加入课程的学习[start] ===============
    public function testBecomeStudent()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course); //????
        $this->getCourseService()->publishCourse($createCourse['id']);
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $member = $this->getCourseService()->becomeStudent($createCourse['id'], $currentUser['id']);
        $this->assertNotEmpty($member);
    }

//============成为学员，即加入课程的学习[end] ===============

    //============退学[start] =====================
    public function testRemoveStudent()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course); //????
        $this->getCourseService()->publishCourse($createCourse['id']);
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $member = $this->getCourseService()->becomeStudent($createCourse['id'], $currentUser['id']);
        $this->getCourseService()->removeStudent($createCourse['id'], $currentUser['id']);
        $result = $this->getCourseService()->isCourseStudent($createCourse['id'], $currentUser['id']);
        $this->assertEquals(false, $result);
    }

//============退学[end] =====================

    //==============封锁学员，封锁之后学员不能再查看该课程[start]=========
    public function testLockStudent()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'], $normalUser['id']);
        $this->getCourseService()->lockStudent($createCourse['id'], $normalUser['id']);
        $result = $this->getCourseService()->getCourseMember($createCourse['id'], $normalUser['id']);
        $this->assertEquals($result['locked'], '1');
    }

//==============封锁学员，封锁之后学员不能再查看该课程[end]=========

    //===============解封学员[start] ==================
    public function testUnlockStudent()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course); //????
        $this->getCourseService()->publishCourse($createCourse['id']);
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'], $normalUser['id']);
        $this->getCourseService()->lockStudent($createCourse['id'], $normalUser['id']);
        $result = $this->getCourseService()->getCourseMember($createCourse['id'], $normalUser['id']);
        $this->assertEquals($result['locked'], '1');
        $this->getCourseService()->unlockStudent($createCourse['id'], $normalUser['id']);
        $result = $this->getCourseService()->getCourseMember($createCourse['id'], $normalUser['id']);
        $this->assertEquals($result['locked'], '0');
    }

//===============解封学员[end] ==================

    //=============尝试管理课程, 无权限则抛出异常[start]======
    public function testTryManageCourse() //????

    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $result = $this->getCourseService()->tryManageCourse($createCourse['id']);
        $this->assertEquals($result['id'], $createCourse['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testTryManageCourseTwice()
    {
        $user   = $this->getCurrentUser();
        $course = array(
            'id' => '1'
        );
        $this->getCourseService()->tryManageCourse($course['id']);
    }

//=============尝试管理课程, 无权限则抛出异常[end]======

    //=============是否可以管理课程,如果课程不存在，且当前操作用户为管理员时，返回true [start]==============
    public function testCanManageCourse()
    {
        $user         = $this->getCurrentUser();
        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->canManageCourse($createCourse['id']);
        $this->assertTrue($result);
    }

    public function testCanManageCourseTwice()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'id'    => '1',
            'title' => 'sss'
        );
        $result = $this->getCourseService()->canManageCourse($course['id']);
        // $this->assertFalse($result);///????
    }

    public function testCanManageCourseThird()
    {
        $user = $this->createTeacherUser();

        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->canManageCourse($createCourse['id']);
        $this->assertTrue($result);
    }

//=============是否可以管理课程,如果课程不存在，且当前操作用户为管理员时，返回true [end]==============

    /**
     * @expectedException Topxia\Common\Exception\ResourceNotFoundException
     */
    public function testTryTakeCourse()
    {
        $this->getCourseService()->TryTakeCourse('1');
    }

    public function testTryTakeCourseTwice()
    {
        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->tryTakeCourse($createCourse['id']);
        $this->assertNotEmpty($result);
    }

    //=============尝试学习课程[start]==============

    public function testTryLearnCourse()
    {
        $user        = $this->createTeacherUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->tryLearnCourse($createCourse['id']);
        $this->assertNotEmpty($result);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testTryLearnCourseTwice()
    {
        $this->getCourseService()->tryLearnCourse('1');
    }

#  /**

# * @expectedException Topxia\Service\Common\ServiceException

# */

    /*public function testTryLearnCourseThird()//???
    {
    $user = $this->createTeacherUser();
    $currentUser = new CurrentUser();
    $currentUser->fromArray($user);
    $this->getServiceKernel()->setCurrentUser($currentUser);

    $course = array(
    'title' => 'online test course 1',
    );
    $createCourse = $this->getCourseService()->createCourse($course);

    $userStudent = $this->createStudentUser();
    $currentUser = new CurrentUser();
    $currentUser->fromArray($userStudent);
    $this->getServiceKernel()->setCurrentUser($currentUser);
    $this->getCourseService()->tryLearnCourse($createCourse['id']);
    }
     */
    //=============尝试学习课程[end]==============

    public function testIncreaseLessonQuizCount()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'text'
        ));

        $this->getCourseService()->increaseLessonQuizCount($lesson['id']);
        $result = $this->getCourseService()->getLesson($lesson['id']);
        $this->assertEquals('1', $result['quizNum']);
    }

    public function testResetLessonQuizCount()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'text'
        ));

        $this->getCourseService()->resetLessonQuizCount($lesson['id'], '3');
        $result = $this->getCourseService()->getLesson($lesson['id']);
        $this->assertEquals('3', $result['quizNum']);
    }

    public function testIncreaseLessonMaterialCount()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'text'
        ));

        $this->getCourseService()->increaseLessonMaterialCount($lesson['id']);
        $result = $this->getCourseService()->getLesson($lesson['id']);
        $this->assertEquals('1', $result['materialNum']);
    }

    public function testResetLessonMaterialCount()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'text'
        ));

        $this->getCourseService()->resetLessonMaterialCount($lesson['id'], '3');
        $result = $this->getCourseService()->getLesson($lesson['id']);
        $this->assertEquals('3', $result['materialNum']);
    }

    public function testSetMemberNoteNumber()
    {
        $user        = $this->createTeacherUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title'    => 'test lesson 1',
            'content'  => 'test lesson content 1',
            'type'     => 'text'
        ));

        $result = $this->getCourseService()->setMemberNoteNumber($course['id'], $currentUser['id'], '2');
        $this->assertTrue($result);
    }

    public function testFavoriteCourse()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $result = $this->getCourseService()->favoriteCourse($createCourse['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFavoriteCourseTwice()
    {
        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result       = $this->getCourseService()->favoriteCourse($createCourse['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFavoriteCourseThird()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->favoriteCourse($course['id']);
        $this->getCourseService()->favoriteCourse($course['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFavoriteCourseForth()
    {
        $course = array(
            'id' => '100'
        );
        $this->getCourseService()->favoriteCourse($course['id']);
    }

    public function testUnFavoriteCourse()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course       = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $this->getCourseService()->favoriteCourse($createCourse['id']);
        $result = $this->getCourseService()->unFavoriteCourse($createCourse['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnFavoriteCourseTwice()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     * @group current
     */
    public function testUnFavoriteCourseThird()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->favoriteCourse($course['id']);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnFavoriteCourseForth()
    {
        $course = array(
            'id' => '100'
        );
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testHasFavoritedCourse()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->assertFalse($this->getCourseService()->hasFavoritedCourse($course['id']));

        $this->getCourseService()->favoriteCourse($course['id']);
        $this->assertTrue($this->getCourseService()->hasFavoritedCourse($course['id']));
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testHasFavoritedCourseTwice()
    {
        $course = array(
            'id' => '100'
        );
        $this->getCourseService()->hasFavoritedCourse($course['id']);
    }

    public function testGenerateLessonReplay()
    {
        /*$course = $this->getCourseService()->createCourse(array(
    'title' => 'online test course 1',
    ));

    $lesson = $this->getCourseService()->createLesson(array(
    'courseId' => $course['id'],
    'title' => 'test lesson 1',
    'content' => 'test lesson content 1',
    'type'=>'text'
    ));

    $result = $this->getCourseService()->generateLessonReplay($course['id'],$lesson['id']);
    // $this->assertEquals*/
    }

    public function testEntryReplay()
    {
    }

    public function testGetCourseLessonReplayByLessonId()
    {
    }

    public function testCreateMemberByClassroomJoined()
    {
        $user = $this->getCurrentUser();

        $textClassroom = array(
            'title'      => 'test',
            'id'         => 1,
            'categoryId' => 1,
            'status'     => 'published'
        );

        $course  = array('title' => 'course');
        $course2 = array('title' => 'course2');

        $course  = $this->getCourseService()->createCourse($course);
        $course2 = $this->getCourseService()->createCourse($course2);
        $this->getCourseService()->publishCourse($course['id']);
        $this->getCourseService()->publishCourse($course2['id']);
        $course    = $this->getCourseService()->getCourse($course['id']);
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($course['id'], $course2['id']));

        $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);

        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $this->getClassroomService()->becomeStudent($classroom['id'], $currentUser['id']);
        $result = $this->getCourseService()->createMemberByClassroomJoined($course['id'], $normalUser['id'], $classroom['id']);
        $this->getCourseService()->isCourseStudent($course2['id'], $normalUser['id']);
    }

    public function testDeleteCourseLessonReplayByLessonId()
    {
    }

    public function testFindCoursesByStudentIdAndCourseIds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course       = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course); //????
        $this->getCourseService()->publishCourse($createCourse['id']);
        $normalUser  = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($normalUser);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'], $currentUser['id']);
        $result = $this->getCourseService()->findCoursesByStudentIdAndCourseIds($currentUser['id'], array($createCourse['id']));
        $this->assertEquals('2', $result[0]['id']);
    }

    public function testBecomeStudentByClassroomJoined()
    {
        $user = $this->getCurrentUser();

        $textClassroom = array(
            'title'      => 'test',
            'id'         => 1,
            'categoryId' => 1,
            'status'     => 'published'
        );

        $course = array(
            'title' => 'course'
        );
        $course = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($course['id']);
        $course    = $this->getCourseService()->getCourse($course['id']);
        $classroom = $this->getClassroomService()->addClassroom($textClassroom);
        $this->getClassroomService()->publishClassroom($classroom['id']);
        $this->getClassroomService()->addCoursesToClassroom($classroom['id'], array($course['id']));

        $this->getClassroomService()->updateClassroom($classroom['id'], $textClassroom);
        $classroom = $this->getClassroomService()->getClassroom($classroom['id']);
        $this->getClassroomService()->findClassroomByCourseId($course['id']);
        $normalUser = $this->createNormalUser();

        $this->getClassroomService()->becomeStudent($classroom['id'], $normalUser['id']);
        $this->getCourseService()->becomeStudentByClassroomJoined($course['id'], $normalUser['id']);
    }

    public function testCreateLessonAndChapter()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 1',
            'type'     => 'chapter'
        ));

        $this->assertEquals(1, $chapter['number']);
        $this->assertEquals(1, $chapter['seq']);

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter['id'],
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));
        $this->assertEquals(1, $lesson['number']);
        $this->assertEquals(2, $lesson['seq']);

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter['id'],
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 2',
            'type'      => 'text'
        ));
        $this->assertEquals(2, $lesson['number']);
        $this->assertEquals(3, $lesson['seq']);

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 2',
            'type'     => 'chapter'
        ));
        $this->assertEquals(2, $chapter['number']);
        $this->assertEquals(4, $chapter['seq']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSortCourseItemsWithLessItemIds()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $items   = $this->createCourseItems($course);
        $itemIds = array_keys($items);
        shuffle($itemIds);
        array_pop($itemIds);
        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSortCourseItemsWithMoreItemIds()
    {
        $course    = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $items     = $this->createCourseItems($course);
        $itemIds   = array_keys($items);
        $itemIds[] = 'lesson-99999';
        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSortCourseItemsWithErrorItemIds()
    {
        $course  = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1'
        ));
        $items   = $this->createCourseItems($course);
        $itemIds = array_keys($items);
        shuffle($itemIds);
        array_pop($itemIds);
        $itemIds[] = 'lesson-99999';
        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);
    }

    protected function createCourseItems($course)
    {
        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 1',
            'type'     => 'chapter'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter['id'],
            'title'     => 'test lesson 1',
            'content'   => 'test lesson content 1',
            'type'      => 'text'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter['id'],
            'title'     => 'test lesson 2',
            'content'   => 'test lesson content 2',
            'type'      => 'text'
        ));

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title'    => 'chapter 2',
            'type'     => 'chapter'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId'  => $course['id'],
            'chapterId' => $chapter['id'],
            'title'     => 'test lesson 3',
            'content'   => 'test lesson content 3',
            'type'      => 'text'
        ));

        return $this->getCourseService()->getCourseItems($course['id']);
    }

    private function createUser()
    {
        $user              = array();
        $user['email']     = "user@user.com";
        $user['nickname']  = "user";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_SUPER_ADMIN', 'ROLE_TEACHER');
        return $user;
    }

    private function createStudentUser()
    {
        $user              = array();
        $user['email']     = "userStudent@userStudent.com";
        $user['nickname']  = "userStudent";
        $user['password']  = "userStudent";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER');
        return $user;
    }

    private function createTeacherUser()
    {
        $user              = array();
        $user['email']     = "teacherUser@user.com";
        $user['nickname']  = "teacherUser";
        $user['password']  = "teacherUser";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER', 'ROLE_TEACHER');
        return $user;
    }

    private function createNormalUser()
    {
        $user              = array();
        $user['email']     = "normal@user.com";
        $user['nickname']  = "normal";
        $user['password']  = "user";
        $user              = $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles']     = array('ROLE_USER');
        return $user;
    }

    /**
     * 　同步课程数据
     */

    public function testFindCoursesByParentIdAndLocked()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course1 = array('title' => 'test-one', 'parentId' => 1, 'locked' => 1, 'userId' => $currentUser['id'], 'createdTime' => time());
        $course1 = $this->getCourseDao()->addCourse($course1);
        $result  = $this->getCourseService()->findCoursesByParentIdAndLocked(1, 1);
        $this->assertEquals(1, count($result));
    }

    public function testFindLessonsByCopyIdAndLockedCourseIds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course1 = array(
            'title' => 'test one'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id']);
        $lesson1       = array(
            'courseId'    => $createCourse1['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'copyId'      => 0,
            'userId'      => 1,
            'createdTime' => time()
        );

        $lesson2        = array(
            'courseId'    => $createCourse1['id'],
            'chapterId'   => 0,
            'free'        => 0,
            'title'       => 'test lesson',
            'number'      => '1',
            'summary'     => '',
            'type'        => 'text',
            'seq'         => '1',
            'copyId'      => 1,
            'userId'      => 1,
            'createdTime' => time()
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $createdLesson2 = $this->getCourseService()->createLesson($lesson2);
        $result         = $this->getCourseService()->findLessonsByCopyIdAndLockedCourseIds($createdLesson2['copyId'], array(1));
        $this->assertEquals(1, count($result));
    }

    public function testFindChaptersByCopyIdAndLockedCourseIds()
    {
        $user        = $this->createUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course1 = array(
            'title' => 'test one'
        );

        $createCourse1   = $this->getCourseService()->createCourse($course1);
        $chapter1        = array('courseId' => $createCourse1['id'], 'title' => 'chapter 1', 'type' => 'chapter', 'copyId' => 1);
        $createdChapter1 = $this->getCourseService()->createChapter($chapter1);
        $chapter         = $this->getCourseService()->findChaptersByCopyIdAndLockedCourseIds(1, array(1));
        $this->assertEquals('chapter 1', $chapter[0]['title']);
    }

    public function testAddCourseLessonReplay()
    {
        $courseLessonReplay = array('lessonId' => 1, 'courseId' => 1, 'title' => '录播回放', 'replayId' => '1', 'userId' => '1', 'createdTime' => time());
        $courseLessonReplay = $this->getCourseService()->addCourseLessonReplay($courseLessonReplay);
        $this->assertEquals('录播回放', $courseLessonReplay['title']);
    }

    public function testGetCourseLessonReplayByCourseIdAndLessonId()
    {
        $courseLessonReplay = array('lessonId' => 1, 'courseId' => 1, 'title' => '录播回放', 'replayId' => '1', 'userId' => '1', 'createdTime' => time());
        $courseLessonReplay = $this->getCourseService()->addCourseLessonReplay($courseLessonReplay);
        $this->assertEquals('录播回放', $courseLessonReplay['title']);
        $courseLessonReplay = $this->getCourseService()->getCourseLessonReplayByCourseIdAndLessonId(1, 1, 'live');
        $this->assertEquals('录播回放', $courseLessonReplay['title']);
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getCourseDao()
    {
        return $this->getServiceKernel()->createDao('Course.CourseDao');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseMemberService()
    {
        return $this->getServiceKernel()->createService('Course.CourseMemberService');
    }
}
