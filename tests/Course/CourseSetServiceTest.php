<?php

namespace Tests\Course;

use Biz\BaseTestCase;

class CourseSetServiceTest extends BaseTestCase
{
    public function testCreate()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
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
            'type'  => 'normal'
        );
        $expected = $this->getCourseSetService()->createCourseSet($courseSet);
        $res      = $this->getCourseSetService()->findCourseSetsLikeTitle('开始');

        $this->assertEquals(array($expected), $res);
    }

    public function testUpdate()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);

        $created['title']         = '新课程开始(更新)！';
        $created['subtitle']      = '新课程的副标题参见！';
        $created['tags']          = 'new';
        $created['categoryId']    = 6;
        $created['serializeMode'] = 'none';
        $updated                  = $this->getCourseSetService()->updateCourseSet($created['id'], $created);
        $this->assertEquals($created['title'], $updated['title']);
    }

    public function testUpdateDetail()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);

        $created['summary']   = 'this is summary <script ...';
        $created['goals']     = array(1);
        $created['audiences'] = array(1);

        $updated = $this->getCourseSetService()->updateCourseSetDetail($created['id'], $created);
        $this->assertEquals($updated['summary'], $created['summary']);
    }

    public function testChangeCover()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        //mock file service ?
        // $updated = $this->getCourseSetService()->changeCourseSetCover($created['id'], array(
        //     'large'  => 1,
        //     'middle' => 2,
        //     'small'  => 3
        // ));
        // $this->assertNotEmpty($updated['cover']);
    }

    public function testDelete()
    {
        $courseSet = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
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
            'type'  => 'normal'
        );
        $created = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertNotEmpty($created);

        $result = $this->getCourseSetService()->updateCourseSetStatistics($created['id'], array('ratingNum'));

        $this->assertEquals(0, $result['ratingNum']);
    }

    public function testApplyDiscount()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type'  => 'normal'
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSet);
        $this->assertNotEmpty($courseSet);

        $discountFields = array(
            'type' => 'discount',
            'startTime' => time(),
            'endTime' => time() + 100,
        );
        $discount = $this->getDiscountService()->addDiscount($discountFields);
        $this->assertNotEmpty($discount);

        $discountItem = $this->getDiscountService()->putItems($discount['id'], array($courseSet['id']));
        $this->assertNotEmpty($discountItem);
        $this->getDiscountService()->setAllItemsDiscount($discount['id'], 5);

        //Todo set course origin price ...
    }

    public function testCancelDiscount()
    {
        ;
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getDiscountService()
    {
        return $this->createService('DiscountPlugin:Discount:DiscountService');
    }
}
