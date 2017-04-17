<?php

namespace Biz\Coupon\Type;

use Biz\Course\Service\CourseService;

class CourseCoupon extends BaseCoupon
{
    /**
     * {@inheritdoc}
     */
    public function canUseable($coupon, $target)
    {
        $course = $this->getCourseService()->getCourse($target['id']);

        return $coupon['targetId'] === $course['courseSetId'];
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }
}
