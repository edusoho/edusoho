<?php

namespace Biz\Coupon\Type;

use Biz\Coupon\Service\CouponBatchResourceService;
use Biz\Course\Service\CourseService;

class CourseCoupon extends BaseCoupon
{
    /**
     * {@inheritdoc}
     */
    public function canUseable($coupon, $target)
    {
        $course = $this->getCourseService()->getCourse($target['id']);

        return $this->getCouponBatchResourceService()->isCouponTarget($coupon['batchId'], $course['courseSetId']);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CouponBatchResourceService
     */
    protected function getCouponBatchResourceService()
    {
        return $this->biz->service('Coupon:CouponBatchResourceService');
    }
}
