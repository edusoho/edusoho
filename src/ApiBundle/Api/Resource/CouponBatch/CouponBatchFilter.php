<?php

namespace ApiBundle\Api\Resource\CouponBatch;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Coupon\CouponFilter;
use VipPlugin\Api\Resource\VipLevel\VipLevelFilter;

class CouponBatchFilter extends Filter
{
    protected $publicFields = array(
        'id', 'name', 'token', 'type', 'prefix', 'generatedNum', 'usedNum', 'receiveNum', 'rate', 'deadlineMode', 'fixedDay', 'deadline', 'unreceivedNum', 'currentUserCoupon', 'targets', 'targetType', 'description', 'createdTime', 'targetDetail', 'targetIds',
    );

    protected function publicFields(&$data)
    {
        $data['deadline'] = $data['deadline'] > 0 ? date('c', $data['deadline']) : '';
        if (!empty($data['targets'])) {
            foreach ($data['targets'] as &$target) {
                $targetFilter = $this->getFilter($data['targetType']);
                $targetFilter->setMode(Filter::SIMPLE_MODE);
                $targetFilter->filter($target);
            }
        }

        if (isset($data['currentUserCoupon'])) {
            $couponFilter = new CouponFilter(Filter::PUBLIC_MODE);
            $couponFilter->filter($data['currentUserCoupon']);
        } else {
            $data['currentUserCoupon'] = null;
        }
    }

    protected function getFilter($type)
    {
        $filters = array(
            'course' => new CourseSetFilter(),
            'classroom' => new ClassroomFilter(),
            'vip' => new VipLevelFilter(),
        );

        return $filters[$type];
    }
}
