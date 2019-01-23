<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Coupon\CouponFilter;
use VipPlugin\Api\Resource\VipLevel\VipLevelFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\MarketingActivity\MarketingActivityFilter;

class PageDiscoveryFilter extends Filter
{
    protected $publicFields = array('type', 'data', 'moduleType');

    protected function publicFields(&$data)
    {
        if ('course_list' == $data['type']) {
            $courseFilter = new CourseFilter();
            $courseFilter->setMode(Filter::PUBLIC_MODE);
            foreach ($data['data']['items'] as &$course) {
                $courseFilter->filter($course);
                unset($course['summary']);
                unset($course['courseSet']['summary']);
            }
        }

        if ('classroom_list' == $data['type']) {
            $classroomFilter = new ClassroomFilter();
            $classroomFilter->setMode(Filter::PUBLIC_MODE);
            foreach ($data['data']['items'] as &$classroom) {
                $classroomFilter->filter($classroom);
                unset($classroom['about']);
            }
        }

        $vipLevelFilter = null;
        if ('vip' == $data['type']) {
            $vipLevelFilter = new VipLevelFilter();
            $vipLevelFilter->setMode(Filter::PUBLIC_MODE);
            foreach ($data['data']['items'] as &$vipLevel) {
                $vipLevelFilter->filter($vipLevel);
            }
        }

        if ('coupon' == $data['type']) {
            $couponFilter = new CouponFilter();
            $couponFilter->setMode(Filter::PUBLIC_MODE);
            foreach ($data['data']['items'] as &$couponBatch) {
                if ('vip' == $couponBatch['targetType'] && !empty($couponBatch['target'])) {
                    $vipLevelFilter = empty($vipLevelFilter) ? new VipLevelFilter() : $vipLevelFilter;
                    $vipLevelFilter->setMode(Filter::PUBLIC_MODE);
                    $vipLevelFilter->filter($couponBatch['target']);
                }
                if ('course' == $couponBatch['targetType'] && !empty($couponBatch['target'])) {
                    unset($couponBatch['target']['summary']);
                }
                if ('classroom' == $couponBatch['targetType'] && !empty($couponBatch['target'])) {
                    unset($couponBatch['target']['about']);
                }
                if (!empty($couponBatch['currentUserCoupon'])) {
                    $couponFilter->filter($couponBatch['currentUserCoupon']);
                }
            }
        }

        if (in_array($data['type'], array('cut', 'seckill', 'groupon'))) {
            $marketingActivityFilter = new MarketingActivityFilter();
            $marketingActivityFilter->setMode(Filter::SIMPLE_MODE);
            $marketingActivityFilter->filter($data['data']['activity']);
        }
    }
}
