<?php

namespace Tests\Course;

use Biz\User\CurrentUser;
use Biz\User\Service\UserService;
use Biz\Course\Service\CourseService;
use Biz\BaseTestCase;;
use Topxia\Service\Common\ServiceKernel;

class CourseServiceTest extends BaseTestCase
{
    public function testFindCoursesByCourseSetId()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);
        $courses = $this->getCourseService()->findCoursesByCourseSetId(1);
        $this->assertEquals(sizeof($courses), 1);
    }

    public function testCreateAndGet()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);
        $this->assertNotNull($result);

        $created = $this->getCourseService()->getCourse($result['id']);
        $this->assertEquals($result['title'], $created['title']);
    }

    public function testUpdate()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);

        $result['title'] = '第一个教学计划(改)';
        unset($result['learnMode']);

        $updated = $this->getCourseService()->updateCourse($result['id'], $result);

        $this->assertEquals($updated['title'], $result['title']);
    }

    public function testUpdateCourseMarketing()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);

        $result['isFree']        = 0;
        $result['price']         = 111;
        $result['vipLevelId']    = 1;
        $result['buyable']       = 1;
        $result['tryLookable']   = 1;
        $result['tryLookLength'] = 2;
        $result['watchLimit']    = 3;
        $result['services']      = array('xxx', 'yy', 'zzz');
        $updated                 = $this->getCourseService()->updateCourseMarketing($result['id'], $result);

        $this->assertEquals($result['price'] * 100, $updated['price']);
        $this->assertEquals($result['vipLevelId'], $updated['vipLevelId']);
        $this->assertEquals($result['buyable'], $updated['buyable']);
        $this->assertEquals($result['tryLookable'], $updated['tryLookable']);
        $this->assertEquals($result['tryLookLength'], $updated['tryLookLength']);
        $this->assertEquals($result['watchLimit'], $updated['watchLimit']);
        $this->assertEquals($result['services'], $updated['services']);
    }

    public function testDelete()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);

        $deleted = $this->getCourseService()->deleteCourse($result['id']);

        $this->assertEquals($deleted, 1);
    }

    public function testCloseCourse()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);
        $this->getCourseService()->publishCourse($result['id'], 1);
        $this->getCourseService()->closeCourse($result['id']);

        $closed = $this->getCourseService()->getCourse($result['id']);

        $this->assertTrue($closed['status'] == 'closed');
    }

    public function testPublishCourse()
    {
        $course = array(
            'title'       => '第一个教学计划',
            'courseSetId' => 1,
            'learnMode'   => 'lockMode',
            'expiryMode'  => 'days',
            'expiryDays'  => 0
        );

        $result = $this->getCourseService()->createCourse($course);

        $this->getCourseService()->publishCourse($result['id'], 1);

        $published = $this->getCourseService()->getCourse($result['id']);
        $this->assertEquals($published['status'], 'published');
    }

    public function testFindLearnedCoursesByCourseIdAndUserId()
    {
        $course1 = array(
            'title'       => 'test course 1',
            'courseSetId' => 1,
            'expiryMode'  => 'days',
            'learnMode'   => 'lockMode'
        );
        $course2 = array(
            'title'       => 'test course 2',
            'courseSetId' => 1,
            'expiryMode'  => 'days',
            'learnMode'   => 'lockMode'

        );
        $createCourse1 = $this->getCourseService()->createCourse($course1);
        $createCourse2 = $this->getCourseService()->createCourse($course2);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse1['id'], 1);
        $publishCourse = $this->getCourseService()->publishCourse($createCourse2['id'], 2);

        $lesson1 = array(
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
        $lesson2 = array(
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

        $user        = $this->createNormalUser();
        $currentUser = new CurrentUser();
        $currentUser->fromArray($user);

        $this->getServiceKernel()->setCurrentUser($currentUser);
        $addCourse1 = $this->getCourseService()->createCourseStudent($createCourse1['id'], array('userId' => $user['id'], 'price' => 0));
        $addCourse2 = $this->getCourseService()->createCourseStudent($createCourse2['id'], array('userId' => $user['id'], 'price' => 0));
        $tryLearn1  = $this->getCourseService()->tryTakeCourse($createCourse1['id']);
        $tryLearn2  = $this->getCourseService()->tryTakeCourse($createCourse2['id']);
        $result     = $this->getCourseService()->findLearnedCoursesByCourseIdAndUserId($createCourse1['id'], $user['id']);
        //  $this->assertCount(1, $result);
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }
}
