<?php

namespace Tests\Unit\Coupon\Type;

use Biz\BaseTestCase;

class CourseCouponTest extends BaseTestCase
{
    public function testCanUseable()
    {
        $biz = $this->getBiz();
        $couponFactory = $biz['coupon_factory'];
        $courseCoupon = $couponFactory('course');
        $courseSet = $this->createNewCourseSet();
        $course = array(
            'title' => 'test',
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => 1,
            'courseType' => 'default',
        );
        $this->getCourseService()->createCourse($course);

        $result = $courseCoupon->canUseable(array('targetId' => '1'), array('id' => 1));
        $this->assertTrue($result);

        $result1 = $courseCoupon->canUseable(array('targetId' => '1'), array('id' => 10));
        $this->assertFalse($result1);
    }

    protected function defaultCourse($title, $courseSet, $isDefault = 1)
    {
        return array(
            'title' => $title,
            'courseSetId' => $courseSet['id'],
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
            'isDefault' => $isDefault,
            'courseType' => $isDefault ? 'default' : 'normal',
        );
    }

    protected function createNewCourseSet()
    {
        $courseSetFields = array(
            'title' => '新课程开始！',
            'type' => 'normal',
        );
        $courseSet = $this->getCourseSetService()->createCourseSet($courseSetFields);

        return $courseSet;
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    private function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
