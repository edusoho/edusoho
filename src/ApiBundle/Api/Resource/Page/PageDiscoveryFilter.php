<?php

namespace ApiBundle\Api\Resource\Page;

use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use ApiBundle\Api\Resource\Coupon\CouponFilter;
use ApiBundle\Api\Resource\Course\CourseFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseFilter;
use ApiBundle\Api\Resource\MarketingActivity\MarketingActivityFilter;
use ApiBundle\Api\Resource\OpenCourse\OpenCourseFilter;
use ApiBundle\Api\Util\AssetHelper;
use VipPlugin\Api\Resource\VipLevel\VipLevelFilter;

class PageDiscoveryFilter extends Filter
{
    protected $publicFields = ['type', 'data', 'moduleType', 'tips'];

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

        if ('slide_show' == $data['type']) {
            foreach ($data['data'] as &$slide) {
                $slide['image']['uri'] = $this->convertFilePath($slide['image']['uri']);
                if(empty($slide['link'])) {
                    $slide['link'] = null;
                }
            }
        }

        if (in_array($data['type'], ['cut', 'seckill', 'groupon'])) {
            $marketingActivityFilter = new MarketingActivityFilter();
            $marketingActivityFilter->setMode(Filter::SIMPLE_MODE);
            $marketingActivityFilter->filter($data['data']['activity']);
        }

        if ('open_course_list' == $data['type']) {
            $courseFilter = new OpenCourseFilter();
            $courseFilter->setMode(Filter::PUBLIC_MODE);
            foreach ($data['data']['items'] as &$course) {
                $courseFilter->filter($course);
            }
        }

        if ('graphic_navigation' == $data['type']) {
            foreach ($data['data'] as &$navigation) {
                if (empty($navigation['image']) || empty($navigation['link'])) {
                    continue;
                }

                if (!empty($navigation['image']['url'])) {
                    continue;
                }

                $default = 'course' == $navigation['link']['type'] ? 'hot_course.png' : ('openCourse' == $navigation['link']['type'] ? 'open_course.png' : 'hot_classroom.png');
                $navigation['image']['url'] = AssetHelper::getFurl('', $default);
            }
        }

        if ('item_bank_exercise' == $data['type']) {
            $itemBankExerciseFilter = new ItemBankExerciseFilter();
            foreach ($data['data']['items'] as &$itemBankExercise) {
                $itemBankExerciseFilter->filter($itemBankExercise);
            }
        }
    }
}
