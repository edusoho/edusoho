<?php
namespace Topxia\Service\Course\Tests;

use Topxia\Service\Common\BaseTestCase;
use Topxia\Service\Course\CourseService;
use Topxia\Service\User\UserService;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\User\CurrentUser;
use Topxia\Service\Taxonomy\TagService;

class CourseServiceTest extends BaseTestCase
{


     //=============== Course API Test [start]===============

    public function testGetCourse()
    {
        $course = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals($course['title'], $result['title']);
    }

    public function testGetCoursesCount()
    {
        $course1 = array(
            'title' => 'online test course 1'
        );
        $course2 = array(
            'title' => 'online test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $result = $this->getCourseService()->getCoursesCount();
        $this->assertEquals(2,$result);
    }

    public function testFindCoursesByIds()
    {
        $course1 = array(
            'title' => 'online test course 1'
        );
        $course2 = array(
            'title' => 'online test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $ids = array(
            $createCourse1['id'],
            $createCourse2['id']
        );
        $result = $this->getCourseService()->findCoursesByIds($ids);
        $this->assertNotEmpty($result);
        $this->assertCount(2,$result);
        $this->assertEquals($result[1]['title'],$course1['title']);
        $this->assertEquals($result[2]['title'],$course2['title']);
    }
    

    public function testFindCoursesByCourseIds()
    {
        $course1 = array(
            'title' => 'online test course 1'
        );
        $course2 = array(
            'title' => 'online test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $ids = array(
            $createCourse1['id'],
            $createCourse2['id']
        );
        $result = $this->getCourseService()->findCoursesByCourseIds($ids,0,1);
        $this->assertNotEmpty($result[1]);
        $this->assertCount(1,$result);
        $this->assertEquals($result[1]['title'],$course1['title']);
    }

    public function  testFindCoursesByLikeTitle()
    {
        $course_like = array(
            'title' => 'online test course 1'
        );
        $course_unlike = array(
            'title' => 'online test course 2'
        );
        $createCourse1 = $this->getCourseService()->createCourse($course_like);
        $createCourse2 = $this->getCourseService()->createCourse($course_unlike);
        $result = $this->getCourseService()->findCoursesByLikeTitle($course_like['title']);
        $this->assertCount(1,$result);
        $this->assertEquals($result[1]['title'],$course_like['title']);
    }

    public function testFindMinStartTimeByCourseId()
    {
        $course = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->findMinStartTimeByCourseId($createCourse['id']);
        $this->assertNotEmpty($result);
    }

    public function testFindCoursesByTagIdsAndStatus()
    {   
        $tags = array(
            'name' => 'tags1',
            'name' => 'tags2',
            'name' => 'tags3'
        );
        $this->getTagService()->addTag($tags);
        $course = array(
            'title' => 'online test course 1',
            'tags' => array('1','2')
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->findCoursesByTagIdsAndStatus(array('1'), 'draft', 0,1 );
        $this->assertNotEmpty($result);
        $this->assertEquals($result[1]['title'],$course['title']);
        // print_r($result);
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
            'tags' => array('1','2')
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->findNormalCoursesByAnyTagIdsAndStatus(array('1'), 'draft',array('Rating' , 'DESC'),0,1 );
        $this->assertNotEmpty($result);
        $this->assertEquals($result[1]['title'],$course['title']);
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

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $createCourse3 = $this->getCourseService()->createCourse($course3);

        $conditions = array(
            'status' => 'draft'
        );

        $result = $this->getCourseService()->searchCourses($conditions,'popular',0,5);
        $this->assertCount(3,$result);
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

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $createCourse3 = $this->getCourseService()->createCourse($course3);

        $result = $this->getCourseService()->searchCourseCount($conditions);
        $this->assertEquals(3,$result);
    }

    public function testFindCoursesCountByLessThanCreatedTime()
    {
        $course1 = array(
            'title' => 'online test course 1'
        );
        $course2 = array(
            'title' => 'online test course 2'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $endTime = strtotime(date("Y-m-d",time()+ 24*3600));
        $result = $this->getCourseService()->findCoursesCountByLessThanCreatedTime($endTime);
        $this->assertEquals($result,2);
    }

    public function testAnalysisCourseSumByTime()
    {
        $course1 = array(
            'title' => 'online test course 1'
        );
        $course2 = array(
            'title' => 'online test course 2'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $endTime = strtotime(date("Y-m-d",time()+ 24*3600));
        $result = $this->getCourseService()->analysisCourseSumByTime($endTime);
        // print_r($result);
        $this->assertEquals($result[0]['count'],2);
    }

    public function testFindUserLearnCourses()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse = $this->getCourseService()->becomeStudent($createCourse['id'],$user['id']);
        $return = $this->getCourseService()->tryLearnCourse($createCourse['id']);
        $result = $this->getCourseService()->findUserLearnCourses($user['id'],0,1);
        $this->assertCount(1,$result);
    }

    public function testFindUserLearnCourseCount()
    {
        $user = $this->createUser(); 
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
        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'],$user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'],$user['id']);
        $tryLearn1 = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2 = $this->getCourseService()->tryLearnCourse($createCourse2['id']);

        $result = $this->getCourseService()->findUserLearnCourseCount($user['id']);
        // print_r($result);
        $this->assertEquals(2,$result);
    }

    public function testFindUserLeaningCourses()
    {
        $user = $this->createUser(); 
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
        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'],$user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'],$user['id']);
        $tryLearn1 = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2 = $this->getCourseService()->tryLearnCourse($createCourse2['id']);

        $result = $this->getCourseService()->findUserLeaningCourses($user['id'], 0,5,array("type"=>"normal"));
        // print_r($result);
        $this->assertCount(2,$result);
    }

    public function testFindUserLeaningCourseCount()
    {
        $user = $this->createUser(); 
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
        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
        ));

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'],$user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'],$user['id']);
        $tryLearn1 = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2 = $this->getCourseService()->tryLearnCourse($createCourse2['id']);

        $result = $this->getCourseService()->findUserLeaningCourseCount($user['id'],array("type"=>"normal"));
        // print_r($result);
        $this->assertEquals(2,$result);
    }

    public function testFindUserLeanedCourseCount()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
            'roles' => $user['roles']
        ));
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
        $lesson1 = array(
            'courseId' => $createCourse1['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $lesson2 = array(
            'courseId' => $createCourse2['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $createdLesson2 = $this->getCourseService()->createLesson($lesson2);


        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'],$user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'],$user['id']);
        $tryLearn1 = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2 = $this->getCourseService()->tryLearnCourse($createCourse2['id']);
        $this->getCourseService()->finishLearnLesson($createCourse1['id'],$createdLesson1['id']);
        $this->getCourseService()->finishLearnLesson($createCourse2['id'],$createdLesson2['id']);
        $result = $this->getCourseService()->findUserLeanedCourseCount($user['id']);
        $this->assertEquals(2,$result);
    }

    public function testFindUserLeanedCourses()
    {
        $user = $this->createUser(); 
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
        $lesson1 = array(
            'courseId' => $createCourse1['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $lesson2 = array(
            'courseId' => $createCourse2['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $createdLesson2 = $this->getCourseService()->createLesson($lesson2);


        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'],$user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'],$user['id']);
        $tryLearn1 = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2 = $this->getCourseService()->tryLearnCourse($createCourse2['id']);
        $this->getCourseService()->finishLearnLesson($createCourse1['id'],$createdLesson1['id']);
        $this->getCourseService()->finishLearnLesson($createCourse2['id'],$createdLesson2['id']);
        $result = $this->getCourseService()->findUserLeanedCourses($user['id'],0,5);
        $this->assertCount(2,$result);
    }

    public function testFindUserTeachCourseCount()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'test course 1'
        );
        $conditions = array(
            'userId' => $user['id']
        );
        $teacher = array(
            'id' =>$user['id']  
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->setCourseTeachers($createCourse['id'],array('0' =>$teacher));
        $result = $this->getCourseService()->findUserTeachCourseCount($conditions,$onlyPublished = false);
        $this->assertEquals(1,$result);


    }

    public function testFindUserTeachCourses()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'test course 1'
        );
        $conditions = array(
            'userId' => $user['id']
        );
        $teacher = array(
            'id' =>$user['id']  
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->setCourseTeachers($createCourse['id'],array('0' =>$teacher));
        $result = $this->getCourseService()->findUserTeachCourses($conditions,0,5,$onlyPublished = false);
        // print_r($result);
        $this->assertCount(1,$result);
    }

    public function testFindUserFavoritedCourseCount()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $this->getCourseService()->favoriteCourse($createCourse['id']);
        $result = $this->getCourseService()->findUserFavoritedCourseCount($user['id']);
        // print_r($result);
        $this->assertEquals(1,$result);
    }

    public function testFindUserFavoritedCourses()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $this->getCourseService()->favoriteCourse($createCourse['id']);
        $result = $this->getCourseService()->findUserFavoritedCourses($user['id'],0,5);
        $this->assertCount(1,$result);
    }

    public function testCreateCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $createCourse = $this->getCourseService()->createCourse($course);

        $this->assertGreaterThan(0, $createCourse['id']);
        $this->assertEquals($course['title'], $createCourse['title']);

    }

    public function testUpdateCourse()
    {
        $course = array(
            'title' => 'online test course1'
        );
        $courseUpdateData = array(
            'title' => 'updateData'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $updateCourse = $this->getCourseService()->updateCourse($createCourse['id'],$courseUpdateData);
        $this->assertEquals('updateData',$updateCourse['title']);
    }

    public function testUpdateCourseCounter()
    {
        $course = array(
            'title' => 'online test course1'
        );
        $counter = array(
            'rating' => '1', 
            'ratingNum' => '1', 
            'lessonNum' => '1', 
            'giveCredit' => '1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $updateCourseCounter = $this->getCourseService()->updateCourseCounter($createCourse['id'],$counter);
        $result = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals(1,$result['rating']);
        $this->assertEquals(1,$result['ratingNum']);
        $this->assertEquals(1,$result['lessonNum']);
        $this->assertEquals(1,$result['giveCredit']);
    }

    public function testChangeCoursePicture()
    {
        //暂时等待
    }

     /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testRecommendCourse()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test title'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->recommendCourse($user['id'],$createCourse['id']);
        $this->assertEquals('test title',$result['title']);
    }

    public function testHitCourse()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test title'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->hitCourse($createCourse['id']);
        $result = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals(1,$result['hitNum']);
    }

    public function testWaveCourse()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test title'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->waveCourse($createCourse['id'],'hitNum',+1);
        $result = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals(1,$result['hitNum']);
    }

     /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testCancelRecommendCourse()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test title'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->recommendCourse($user['id'],$createCourse['id']);
        $this->assertEquals('test title',$result['title']);
        $changeResult = $this->getCourseService()->cancelRecommendCourse($user['id']);
        $this->assertEmpty($changeResult);
        $this->assertEquals($createCourse['recommended'],0);
        $this->assertEquals($createCourse['recommendedTime'],0);
        $this->assertEquals($createCourse['recommendedSeq'],0);
    }

    public function testAnalysisCourseDataByTime()
    {
        $course = array(
            'title' => 'test 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $startTime = strtotime(date("Y-m-d",time()- 24*3600));
        $endTime = strtotime(date("Y-m-d",time()+ 24*3600));
        $result = $this->getCourseService()->analysisCourseDataByTime($startTime,$endTime);
        // print_r($result);
        $this->assertEquals('1',$result[0]['count']);
    }

    public function testFindLearnedCoursesByCourseIdAndUserId()
    {
        $user = $this->createUser(); 
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
        $lesson1 = array(
            'courseId' => $createCourse1['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $lesson2 = array(
            'courseId' => $createCourse2['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $createdLesson2 = $this->getCourseService()->createLesson($lesson2);


        $user = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->becomeStudent($createCourse1['id'],$user['id']);
        $addCourse2 = $this->getCourseService()->becomeStudent($createCourse2['id'],$user['id']);
        $tryLearn1 = $this->getCourseService()->tryLearnCourse($createCourse1['id']);
        $tryLearn2 = $this->getCourseService()->tryLearnCourse($createCourse2['id']);
        $this->getCourseService()->finishLearnLesson($createCourse1['id'],$createdLesson1['id']);
        $result = $this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($createCourse1['id'],$user['id']);
        // print_r($result);
        $this->assertCount(1,$result);

    }

    public function testUploadCourseFile()
    {
        //暂时等待
    }

    public function testSetCoursePrice()
    {
        $course = array(
            'title' => 'test course'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $setPrice = $this->getCourseService()->setCoursePrice($createCourse['id'],'default',100);
        // print_r($setPrice);
        $this->assertEquals(100,$setPrice['originPrice']);
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
        
        $user = $this->createUser(); 
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

        $publishCourse1 = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse2 = $this->getCourseService()->publishCourse($createCourse2['id']);

        $conditions = array(
            'userId' => $user['id']
        );
        $findUserTeachCourses = $this->getCourseService()->findUserTeachCourses($conditions,0,5);
        $this->assertCount(2,$findUserTeachCourses);
        $this->getCourseService()->deleteCourse($createCourse1['id']);
        $findUserTeachCourses = $this->getCourseService()->findUserTeachCourses($conditions,0,5);
        $this->assertCount(1,$findUserTeachCourses);


    }

    public function testPublishCourse()
    {
        $user = $this->createUser(); 
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

        $publishCourse1 = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse2 = $this->getCourseService()->publishCourse($createCourse2['id']);
        $result1 = $this->getCourseService()->getCourse($createCourse1['id']);
        $result2 = $this->getCourseService()->getCourse($createCourse2['id']);
        $this->assertEquals($result1['status'],'published');
        $this->assertEquals($result2['status'],'published');
    }

    public function testCloseCourse()
    {
        $user = $this->createUser(); 
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

        $publishCourse1 = $this->getCourseService()->publishCourse($createCourse1['id']);
        $publishCourse2 = $this->getCourseService()->publishCourse($createCourse2['id']);
        $this->getCourseService()->closeCourse($createCourse1['id']);
        $result1 = $this->getCourseService()->getCourse($createCourse1['id']);
        $result2 = $this->getCourseService()->getCourse($createCourse2['id']);
        $this->assertEquals('closed',$result1['status']);
        $this->assertEquals('published',$result2['status']);
    }

    //================= 课程CRUD [end] ===================


    //================= Lesson API [start] ==================

    public function testFindLessonsByIds()
    {
        $course = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);

        $ids = array(
            $createLesson1['id'],
            $createLesson2['id']
        );
        $result = $this->getCourseService()->findLessonsByIds($ids);
        $this->assertCount(2,$result);

    }

    public function testGetCourseLesson()
    {
        $course = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);
        $result = $this->getCourseService()->getCourseLesson($createCourse['id'],$createLesson1['id']);
        $this->assertEquals($result['id'],$createLesson1['id']);




    }

    public function testFindCourseDraft()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft = array(
            'userId' => $user['id'],
            'title' => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $result = $this->getCourseService()->findCourseDraft($createCourse['id'],$createLesson['id'],$user['id']);
        $this->assertEquals($result['id'],$createCourse['id']);
    }

    public function testGetCourseLessons()
    {
        $course = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);
        $result = $this->getCourseService()->getCourseLessons($createCourse['id']);
        $this->assertCount(2,$result);
    }

    public function testDeleteCourseDrafts()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft = array(
            'userId' => $user['id'],
            'title' => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $result1 = $this->getCourseService()->findCourseDraft($createCourse['id'],$createLesson['id'],$user['id']);
        $this->assertEquals('test course 1',$result1['title']);
        $this->getCourseService()->deleteCourseDrafts($createCourse['id'],$createLesson['id'],$user['id']);
        $result2 = $this->getCourseService()->findCourseDraft($createCourse['id'],$createLesson['id'],$user['id']);
        $this->assertEmpty($result2);
    }

    public function testFindLessonsByTypeAndMediaId()
    {

    }

    public function testSearchLessons()
    {
        $course = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);
        $conditions = array(
            'courseId' => $createCourse['id']
        );
        $result = $this->getCourseService()->searchLessons($conditions,array('createdTime', 'ASC'),0,5);
        $this->assertCount(2,$result);
    }

    public function testSearchLessonCount()
    {
        $course = array(
            'title' => 'online test course1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson1 = $this->getCourseService()->createLesson($lesson);
        $createLesson2 = $this->getCourseService()->createLesson($lesson);
        $conditions = array(
            'courseId' => $createCourse['id']
        );
        $result = $this->getCourseService()->searchLessonCount($conditions);
        $this->assertEquals(2,$result);
    }

    public function testCreateLesson()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $lesson = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);

        $this->assertGreaterThan(0,  $createdLesson['id']);
        $this->assertEquals($lesson['courseId'], $createdLesson['courseId']);
        $this->assertEquals($lesson['title'], $createdLesson['title']);
        $this->assertEquals($lesson['content'], $createdLesson['content']);
        $this->assertEquals(1, $createdLesson['number']);
        $this->assertEquals(1, $createdLesson['seq']);

        $lesson = array(
            'courseId' => $course['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
            'type' => 'text'
        );
        $createdLesson = $this->getCourseService()->createLesson($lesson);

        $this->assertEquals(2, $createdLesson['number']);
        $this->assertEquals(2, $createdLesson['seq']);
    }

    public function testGetCourseDraft()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft = array(
            'userId' => $user['id'],
            'title' => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $result = $this->getCourseService()->getCourseDraft($createCourse['id']);
        $this->assertEquals('test course 1',$result['title']);
    }

    public function testCreateCourseDraft()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft = array(
            'userId' => $user['id'],
            'title' => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $result = $this->getCourseService()->getCourseDraft($createCourse['id']);
        $this->assertEquals(1,$result['id']);
    }

    public function testUpdateLesson()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $this->assertEquals($createLesson['summary'],'');
        $newLesson = array(
            'chapterId' => 0,
            'summary' => 'addSummary'
        );
        $result = $this->getCourseService()->updateLesson($createCourse['id'],$createLesson['id'],$newLesson);
        $this->assertEquals($result['summary'],'addSummary');
    }

    public function testUpdateCourseDraft()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $draft = array(
            'userId' => $user['id'],
            'title' => $course['title'],
            'courseId' => $createCourse['id'],
            'lessonId' => $createLesson['id']
        );
        $this->getCourseService()->createCourseDraft($draft);
        $newDraft = array(
            'title' => 'newDraft'
        );
        $this->getCourseService()->updateCourseDraft($createCourse['id'],$createLesson['id'],$user['id'],$newDraft);
        $result = $this->getCourseService()->getCourseDraft($createCourse['id']);
        $this->assertEquals($result['title'],'newDraft');
    }

    public function testDeleteLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $lesson1 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));

        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
            'type' => 'text'
        ));

        $lesson3 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'title' => 'test lesson 3',
            'content' => 'test lesson content 3',
            'type' => 'text'
        ));

        $this->getCourseService()->deleteLesson($course['id'], $lesson1['id']);

        $this->assertNull($this->getCourseService()->getCourseLesson($course['id'], $lesson1['id']));

        // @FIXME
        // $number = 1;
        // $lessons = $this->getCourseService()->getCourseLessons($course['id']);
        // foreach ($lessons as $lesson) {
        //     $this->assertEquals($number, $lesson['number']);
        //     $number ++;
        // }
    }

    public function testPublishLesson()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson =$this->getCourseService()->publishLesson($createLesson['id'],$createLesson['id']);
        $result = $this->getCourseService()->getCourse($createCourse['id']);
        $this->assertEquals($result['status'],'published');


    }

    public function testUnpublishLesson()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson =$this->getCourseService()->publishLesson($createLesson['id'],$createLesson['id']);
        $result = $this->getCourseService()->getCourseLesson($createCourse['id'],$createLesson['id']);
        $this->assertEquals($result['status'],'published');
        $unPublishLesson =$this->getCourseService()->unpublishLesson($createLesson['id'],$createLesson['id']);
        $result = $this->getCourseService()->getCourseLesson($createCourse['id'],$createLesson['id']);
        $this->assertEquals($result['status'],'unpublished');

    }

    public function testGetNextLessonNumber()
    {
        //此方法已经在createlesson测试完毕，无需再测试
    }

    public function testLiveLessonTimeCheck()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'live',
            'startTime' => '',
            'length' => ''
        );
        $start = strtotime(date("Y-m-d",time()));
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
        $publishLesson =$this->getCourseService()->publishLesson($createLesson['id'],$createLesson['id']);
        $liveLessonTimeCheck = $this->getCourseService()->liveLessonTimeCheck($createCourse['id'],$createLesson['id'],$start,10000);
        $this->assertEquals('error_timeout',$liveLessonTimeCheck[0]);
        $liveLessonTimeCheck = $this->getCourseService()->liveLessonTimeCheck($createCourse['id'],$createLesson['id'],$start,100);
        $this->assertEquals('success',$liveLessonTimeCheck[0]);
    }

    public function testCalculateLiveCourseLeftCapacityInTimeRange()
    {
        //waiting ti know
    }

    public function testCanLearnLesson()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = array(
            'title' => 'test course 1'
        );
        
        $createCourse = $this->getCourseService()->createCourse($course);
        $lesson = array(
            'courseId' => $createCourse['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test'+rand(),
            'summary' => '',
            'type' => 'text',
            'startTime' => '',
            'length' => ''
        );
        $createLesson = $this->getCourseService()->createLesson($lesson);
        $this->getCourseService()->publishCourse($createCourse['id']);
        $user = $this->createNormalUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->getCourseService()->becomeStudent($createCourse['id'],$user['id']);
        $result = $this->getCourseService()->canLearnLesson($createCourse['id'],$createLesson['id']);
        $this->assertEquals('yes',$result['status']);
    }

    public function testStartLearnLesson()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
            'roles' => $user['roles']
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => 0,
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->startLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'],  $course['id'], $lesson['id']);
        $this->assertEquals('learning', $status);
    }

    public function testCreateLessonView()
    {

    }

    public function testFinishLearnLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => 0,
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'],  $course['id'], $lesson['id']);
        $this->assertEquals('finished', $status);
    }

    public function testFindLatestFinishedLearns()
    {

    }

    public function testCancelLearnLesson()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => 0,
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' =>'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $this->getCourseService()->cancelLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'],  $course['id'], $lesson['id']);
        $this->assertEquals('learning', $status);
    }

    public function testGetUserLearnLessonStatus()
    {

    }

    public function testGetUserLearnLessonStatuses()
    {

    }

    public function testFindUserLearnedLessons()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => 0,
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' =>'text'
        ));

        $user = $this->getCurrentUser();

        $this->getCourseService()->finishLearnLesson($course['id'], $lesson['id']);
        $userLearns = $this->getCourseService()->findUserLearnedLessons($user['id'], $course['id']);
        $this->assertEquals(1, count($userLearns));
    }

    public function testGetUserNextLearnLesson()
    {

    }

    public function testSearchLearnCount()
    {

    }

    public function testSearchLearns()
    {

    }

    public function testAnalysisLessonDataByTime()
    {

    }

    public function testAnalysisLessonFinishedDataByTime()
    {

    }

    public function testSearchAnalysisLessonViewCount()
    {

    }

    public function testGetAnalysisLessonMinTime()
    {

    }

    public function testSearchAnalysisLessonView()
    {

    }

    public function testAnalysisLessonViewDataByTime()
    {

    }

    public function testWaveLearningTime()
    {

    }

    public function testFindLearnsCountByLessonId()
    {

    }

    public function testWaveWatchingTime()
    {

    }

    public function testSearchLearnTime()
    {

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

    }

    public function testGetCourseChapters()
    {

    }

    public function testCreateChapter()
    {
        $chapter1 = array('courseId' => 1, 'title' => 'chapter 1', 'type' => 'chapter');
        $chapter2 = array('courseId' => 1, 'title' => 'chapter 2', 'type' => 'chapter');
        $chapter3 = array('courseId' => 1, 'title' => 'chapter 3', 'type' => 'chapter');

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

    }

    public function testDeleteChapter()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
            'roles' => $user['roles']
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);


        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $chapter1 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 1',
            'type' => 'chapter'
        ));

        $lesson1 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter1['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
             'type'=>'text'
        ));

        $lesson2 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter1['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
             'type'=>'text'
        ));

        $chapter2 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 2',
            'type' => 'chapter'
        ));

        $lesson3 = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter2['id'],
            'title' => 'test lesson 3',
            'content' => 'test lesson content 3',
             'type'=>'text'
        ));

        $chapter3 = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 3',
            'type' => 'chapter'
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

    public function testGetNextChapterNumber()
    {

    }

//================ Chapter API[end] ==================

//===========获得课程的目录项[start] =======================
    public function testGetCourseItems()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);

        $this->assertEquals(5, count($items));
        $seq = 1;
        foreach ($items as $item) {
            $this->assertEquals($seq, $item['seq']);
            $seq++; 
        }
    }

    public function testSortCourseItems()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);
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

    }

    public function testSearchMember()
    {

    }

    public function testCountMembersByStartTimeAndEndTime()
    {

    }

    public function testSearchMemberCount()
    {

    }

    public function testFindWillOverdueCourses()
    {

    }

    public function testGetCourseMember()
    {

    }

    public function testSearchMemberIds()
    {

    }

    public function testUpdateCourseMember()
    {

    }

    public function testIsMemberNonExpired()
    {

    }
    public function testFindCourseStudents()
    {

    }

    public function testFindCourseStudentsByCourseIds()
    {

    }

    public function testGetCourseStudentCount()
    {

    }

    public function testFindCourseTeachers()
    {

    }

    public function testIsCourseTeacher()
    {

    }

    public function testIsCourseStudent()
    {

    }

    public function testSetCourseTeachers()
    {

    }

    public function testCancelTeacherInAllCourses()
    {

    }

    public function testRemarkStudent()
    {

    }
    //============Member API[end] ===============

    //============成为学员，即加入课程的学习[start] ===============
    public function testBecomeStudent()
    {

    }
    //============成为学员，即加入课程的学习[end] ===============

    //============退学[start] =====================
    public function testRemoveStudent()
    {

    }
    //============退学[end] =====================

    //==============封锁学员，封锁之后学员不能再查看该课程[start]=========
    public function testLockStudent()
    {

    }
    //==============封锁学员，封锁之后学员不能再查看该课程[end]=========

    //===============解封学员[start] ==================
    public function testUnlockStudent()
    {

    }

    //===============解封学员[end] ==================

    //=============尝试管理课程, 无权限则抛出异常[start]======
    public function testTryManageCourse()
    {

    }
    //=============尝试管理课程, 无权限则抛出异常[end]======

    //=============是否可以管理课程,如果课程不存在，且当前操作用户为管理员时，返回true [start]==============
    public function testCanManageCourse()
    {

    }

    //=============是否可以管理课程,如果课程不存在，且当前操作用户为管理员时，返回true [end]==============


    //=============尝试学习课程[start]==============

    public function testTryLearnCourse()
    {

    }
    //=============尝试学习课程[end]==============

    public function testIncreaseLessonQuizCount()
    {

    }

    public function testResetLessonQuizCount()
    {

    }

    public function testIncreaseLessonMaterialCount()
    {

    }

    public function testResetLessonMaterialCount()
    {

    }
    public function testSetMemberNoteNumber()
    {

    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFavoriteCourse()
    {
        
        $course = array(
            'title' => 'online test course 1'
        );
        $createCourse = $this->getCourseService()->createCourse($course);
        $result = $this->getCourseService()->favoriteCourse($createCourse['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testFavoriteCourseTwice()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->favoriteCourse($course['id']);
        $this->getCourseService()->favoriteCourse($course['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testUnFavoriteCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->favoriteCourse($course['id']);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
        $this->assertTrue($result);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     * @group current
     */
    public function testUnFavoriteCourseTwice()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->favoriteCourse($course['id']);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
        $result = $this->getCourseService()->unFavoriteCourse($course['id']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testHasFavoritedCourse()
    {
        $course = array(
            'title' => 'online test course 1',
        );
        $course = $this->getCourseService()->createCourse($course);

        $this->assertFalse($this->getCourseService()->hasFavoritedCourse($course['id']));

        $this->getCourseService()->favoriteCourse($course['id']);
        $this->assertTrue($this->getCourseService()->hasFavoritedCourse($course['id']));
    }

    public function testGenerateLessonReplay()
    {

    }

    public function testEntryReplay()
    {

    }

    public function testGetCourseLessonReplayByLessonId()
    {

    }

    public function testCreateMemberByClassroomJoined()
    {

    }

    public function testDeleteCourseLessonReplayByLessonId()
    {

    }

    public function testFindCoursesByStudentIdAndCourseIds()
    {

    }

    public function testBecomeStudentByClassroomJoined()
    {

    }

    public function testCreateLessonAndChapter()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 1',
            'type' => 'chapter'
        ));

        $this->assertEquals(1, $chapter['number']);
        $this->assertEquals(1, $chapter['seq']);

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type'=>'text'
        ));
        $this->assertEquals(1, $lesson['number']);
        $this->assertEquals(2, $lesson['seq']);

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
             'type'=>'text'
        ));
        $this->assertEquals(2, $lesson['number']);
        $this->assertEquals(3, $lesson['seq']);

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 2',
            'type' => 'chapter'
        ));
        $this->assertEquals(2, $chapter['number']);
        $this->assertEquals(4, $chapter['seq']);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSortCourseItemsWithLessItemIds()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);
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
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);
        $itemIds = array_keys($items);
        $itemIds[] = 'lesson-99999';
        $this->getCourseService()->sortCourseItems($course['id'], $itemIds);
    }

    /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testSortCourseItemsWithErrorItemIds()
    {
        $course = $this->getCourseService()->createCourse(array(
            'title' => 'online test course 1',
        ));
        $items = $this->createCourseItems($course);
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
            'title' => 'chapter 1',
            'type' => 'chapter'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 1',
            'content' => 'test lesson content 1',
            'type' => 'text'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 2',
            'content' => 'test lesson content 2',
            'type' => 'text'
        ));

        $chapter = $this->getCourseService()->createChapter(array(
            'courseId' => $course['id'],
            'title' => 'chapter 2',
            'type' => 'chapter'
        ));

        $lesson = $this->getCourseService()->createLesson(array(
            'courseId' => $course['id'],
            'chapterId' => $chapter['id'],
            'title' => 'test lesson 3',
            'content' => 'test lesson content 3',
            'type' => 'text'
        ));

        return $this->getCourseService()->getCourseItems($course['id']);
    }

    private function createUser()
    {
        $user = array();
        $user['email'] = "user@user.com";
        $user['nickname'] = "user";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');
        return $user;

    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = "normal@user.com";
        $user['nickname'] = "normal";
        $user['password'] = "user";
        $user =  $this->getUserService()->register($user);
        $user['currentIp'] = '127.0.0.1';
        $user['roles'] = array('ROLE_USER');
        return $user;
    }


    /**
     *　同步课程数据
     */

    public function testFindCoursesByParentIdAndLocked()
    {
        $course1 = array('title' => 'test-one');
        $course2 = array('title' => 'test-two');
        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $this->getCourseService()->editCourse(1, array('parentId'=>1,'locked'=>1));
        $this->getCourseService()->editCourse(2, array('parentId'=>1,'locked'=>1));
        $result = $this->getCourseService()->findCoursesByParentIdAndLocked(1,1);
        $this->assertEquals(2,count($result));
    }

    public function testEditCourse()
    {
        $course1 = array('title' => 'test-one');
        $course2 = array('title' => 'test-two');
        $course1 = $this->getCourseService()->createCourse($course1);
        $course2 = $this->getCourseService()->createCourse($course2);
        $editCourse1 = $this->getCourseService()->editCourse(1, array('title'=>'test-three'));
        $editCourse2 = $this->getCourseService()->editCourse(2, array('title'=>'test-four'));
        $this->assertEquals('test-three',$editCourse1['title']);
        $this->assertEquals('test-four',$editCourse2['title']);
    }

    public function testFindLessonByParentIdAndLockedCourseIds()
    {

        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course1 = array(
            'title' => 'test one'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id']);
        $lesson1 = array(
            'courseId' =>$createCourse1['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test',
            'summary' => '',
            'type' => 'text',
            'parentId'=> 1
        );
        $createdLesson1 = $this->getCourseService()->createLesson($lesson1);
        $this->getCourseService()->editLesson(1,1,array('parentId'=>1));
        $result = $this->getCourseService()->findLessonByParentIdAndLockedCourseIds(1,array(1));
        $this->assertEquals('test', $createdLesson1['title']);
        $this->assertEquals(1, count($result));  
    }

    public function testAddLesson()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course1 = array(
            'title' => 'test one'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id']);
        $lesson1 = array(
            'courseId' =>$createCourse1['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test lesson',
            'summary' => '',
            'type' => 'text',
            'parentId'=> 1
        );
        $createdLesson1 = $this->getCourseService()->addLesson($lesson1);
        $this->assertEquals('test lesson',$createdLesson1['title']);
    }

    public function testEditLesson()
    {

        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course1 = array(
            'title' => 'test one'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $lesson1 = array(
            'courseId' =>$createCourse1['id'],
            'chapterId' => 0,
            'free' => 0,
            'title' => 'test lesson',
            'summary' => '',
            'type' => 'text',
            'parentId'=> 1
        );
        $createdLesson1 = $this->getCourseService()->addLesson($lesson1);

        $editLesson = $this->getCourseService()->editLesson($createCourse1['id'],$createdLesson1['id'],array("title"=>"edit lesson"));
        
        $this->assertEquals("edit lesson", $editLesson['title']);

    }

    public function testAddChapter()
    {
        $chapter1 = array('courseId' => 1, 'title' => 'chapter 1', 'type' => 'chapter');
        $chapter2 = array('courseId' => 1, 'title' => 'chapter 2', 'type' => 'chapter');
        $chapter3 = array('courseId' => 1, 'title' => 'chapter 3', 'type' => 'chapter');

        $createdChapter1 = $this->getCourseService()->addChapter($chapter1);

        $this->assertTrue(is_array($createdChapter1));
        $this->assertEquals($chapter1['courseId'], $createdChapter1['courseId']);
        $this->assertEquals($chapter1['title'], $createdChapter1['title']);


        $createdChapter2 = $this->getCourseService()->addChapter($chapter2);
        $this->assertEquals($chapter2['courseId'], $createdChapter2['courseId']);
        $this->assertEquals($chapter2['title'], $createdChapter2['title']);

        $createdChapter3 = $this->getCourseService()->addChapter($chapter3);
        $this->assertEquals($chapter3['courseId'], $createdChapter3['courseId']);
        $this->assertEquals($chapter3['title'], $createdChapter3['title']);
    }

    public function testEditChapter()
    {
        $chapter1 = array('courseId' => 1, 'title' => 'chapter 1', 'type' => 'chapter'); 
        $createdChapter1 = $this->getCourseService()->addChapter($chapter1);
        $chapter = $this->getCourseService()->editChapter(1,array('title'=>'chapter edit'));
        $this->assertEquals('chapter edit', $chapter['title']);
    }

    public function testFindChapterByChapterIdAndLockedCourseIds()
    {
        $user = $this->createUser(); 
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $course1 = array(
            'title' => 'test one'
        );

        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $chapter1 = array('courseId' => $createCourse1['id'], 'title' => 'chapter 1', 'type' => 'chapter','pId'=>1);  
        $createdChapter1 = $this->getCourseService()->addChapter($chapter1);
        $chapter = $this->getCourseService()->findChapterByChapterIdAndLockedCourseIds(1,array(1));
        $this->assertEquals('chapter 1',$chapter[0]['title']);
    }

    public function testCreateMember()
    {
        $member = array('courseId'=>1);
        $member = $this->getCourseService()->createMember($member);
        $count = $this->getCourseService()->searchMemberCount(array('courseId'=>1));
        $this->assertEquals(1, $count);
    }

    public function testDeleteMemberByCourseIdAndUserId()
    {
        $member = array('courseId'=>1,'userId'=>1);
        $member = $this->getCourseService()->createMember($member);
        $this->assertTrue(is_array($member));
        $count = $this->getCourseService()->deleteMemberByCourseIdAndUserId(1,1);
        $this->assertEquals(1, $count);
    }
    
    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

}