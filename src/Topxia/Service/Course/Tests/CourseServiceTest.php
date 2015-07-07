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
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
            'roles' => $user['roles']
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $course = array(
            'title' => 'test course 1'
        );

        $createCourse = $this->getCourseService()->createCourse($course);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse['id']);
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
        $addCourse = $this->getCourseService()->becomeStudent($createCourse['id'],$user['id']);
        $return = $this->getCourseService()->tryLearnCourse($createCourse['id']);
        $result = $this->getCourseService()->findUserLearnCourses($user['id'],0,1);
        $this->assertCount(1,$result);
    }

    public function testFindUserLearnCourseCount()
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

        $result = $this->getCourseService()->findUserLearnCourseCount($user['id']);
        // print_r($result);
        $this->assertEquals(2,$result);
    }

    public function testFindUserLeaningCourses()
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
        $this->getCourseService()->finishLearnLesson($createCourse1['id'],$createdLesson1['id']);
        $this->getCourseService()->finishLearnLesson($createCourse2['id'],$createdLesson2['id']);
        $result = $this->getCourseService()->findUserLeanedCourseCount($user['id']);
        $this->assertEquals(2,$result);
    }

    public function testFindUserLeanedCourses()
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
        $this->getCourseService()->finishLearnLesson($createCourse1['id'],$createdLesson1['id']);
        $this->getCourseService()->finishLearnLesson($createCourse2['id'],$createdLesson2['id']);
        $result = $this->getCourseService()->findUserLeanedCourses($user['id'],0,5);
        $this->assertCount(2,$result);
    }

    public function testFindUserTeachCourseCount()
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
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
            'roles' => $user['roles']
        ));
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
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
            'roles' => $user['roles']
        ));
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
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
            'roles' => $user['roles']
        ));
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
        //can not test
    }

     /**
     * @expectedException Topxia\Service\Common\ServiceException
     */
    public function testRecommendCourse()
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
        $currentUser->fromArray(array(
            'id' => $user['id'],
            'nickname' => $user['nickname'],
            'email' => $user['email'],
            'password' => $user['password'],
            'currentIp' => '127.0.0.1',
            'roles' => $user['roles']
        ));
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

    }

    public function testCancelRecommendCourse()
    {

    }

    public function testAnalysisCourseDataByTime()
    {

    }

    public function testFindLearnedCoursesByCourseIdAndUserId()
    {

    }

    public function testUploadCourseFile()
    {

    }

    public function testSetCoursePrice()
    {

    }

    public function testSetCoursesPriceWithDiscount()
    {

    }

    public function testRevertCoursesPriceWithDiscount()
    {

    }

    //=============== Course API Test [end] ================


    //================= 课程CRUD [start] ==================

    public function testDeleteCourse()
    {

    }

    public function testPublishCourse()
    {

    }

    public function testCloseCourse()
    {

    }

    //================= 课程CRUD [end] ===================
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

    
    public function testBecomeStudentByClassroomJoined()
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

    public function testStartLearnLesson()
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

        $this->getCourseService()->startLearnLesson($course['id'], $lesson['id']);

        $status = $this->getCourseService()->getUserLearnLessonStatus($user['id'],  $course['id'], $lesson['id']);
        $this->assertEquals('learning', $status);
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
        $user['roles'] = array('ROLE_USER','ROLE_SUPER_ADMIN','ROLE_TEACHER');

        return $this->getUserService()->register($user);
    }

    private function createNormalUser()
    {
        $user = array();
        $user['email'] = "normal@user.com";
        $user['nickname'] = "normalUser";
        $user['password'] = "user";
        $user['roles'] = array('ROLE_USER');

        return $this->getUserService()->register($user);
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