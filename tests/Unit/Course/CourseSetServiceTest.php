<?php

namespace Tests\Unit\Course;

use Biz\BaseTestCase;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;

class CourseSetServiceTest extends BaseTestCase
{
    public function testCreateNormal()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(count($courses) === 1);
        $this->assertTrue($courses[0]['isDefault'] == 1);

        $course = array_shift($courses);
        $this->assertEquals('freeMode', $course['learnMode']);
    }

    public function testCreateNormalCourseSetWithDefaultCourseMode()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
            'learnMode' => 'lockMode'
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(sizeof($courses) === 1);
        $this->assertTrue($courses[0]['isDefault'] == 1);

        $course = array_shift($courses);
        $this->assertEquals('lockMode', $course['learnMode']);
    }


    public function testCreateLive()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'live',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(sizeof($courses) === 1);
        $this->assertTrue($courses[0]['isDefault'] == 1);
    }

    public function testCreateLiveOpen()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'liveOpen',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(sizeof($courses) === 1);
        $this->assertTrue($courses[0]['isDefault'] == 1);
    }

    public function testCreateOpen()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'open',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(sizeof($courses) === 1);
        $this->assertTrue($courses[0]['isDefault'] == 1);
    }

    /**
     * @expectedException  \Codeages\Biz\Framework\Service\Exception\InvalidArgumentException
     */
    public function testCreateErrorType()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'ope2n',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertTrue($created['id'] > 0);
        $courses = $this->getCourseService()->findCoursesByCourseSetId($created['id']);
        $this->assertTrue(sizeof($courses) === 1);
        $this->assertTrue($courses[0]['isDefault'] == 1);
    }


    public function testFindCourseSetsLikeTitle()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $expected = $this->getCourseSetService()->createCourseSet($courseSet);
        $res = $this->getCourseSetService()->findCourseSetsLikeTitle('开始');

        $this->assertEquals($expected['title'], $res[0]['title']);
    }

    public function testUpdate()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);

        $created['title'] = '新课程开始(更新)！';
        $created['subtitle'] = '新课程的副标题参见！';
        $created['tags'] = 'new';
        $created['categoryId'] = 6;
        $created['serializeMode'] = 'none';
        $updated = $this->getCourseSetService()->updateCourseSet($created['id'], $created);
        $this->assertEquals($created['title'], $updated['title']);
    }

    public function testUpdateDetail()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);

        $created['summary'] = 'this is summary <script ...';
        $created['goals'] = array(1);
        $created['audiences'] = array(1);

        $updated = $this->getCourseSetService()->updateCourseSetDetail($created['id'], $created);
        $this->assertEquals($updated['summary'], $created['summary']);
    }

    public function testChangeCover()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $updated = $this->getCourseSetService()->changeCourseSetCover($created['id'], array(
            'large' => 1,
            'middle' => 2,
            'small' => 3
        ));
        $this->assertNotEmpty($updated['cover']);
    }

    public function testDelete()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertNotEmpty($created);
        $this->getCourseSetService()->deleteCourseSet($created['id']);
        $deleted = $this->getCourseSetService()->getCourseSet($created['id']);
        $this->assertEmpty($deleted);
    }

    public function testUpdateCourseSetStatistics()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertNotEmpty($created);

        $result = $this->getCourseSetService()->updateCourseSetStatistics($created['id'], array('ratingNum'));

        $this->assertEquals(0, $result['ratingNum']);
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }


    protected function getDiscountService()
    {
        return $this->createService('DiscountPlugin:Discount:DiscountService');
    }
}
