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
        $activity = json_decode('{"id":"566","merchant_id":"22","type":"cut","name":"\u300a\u5fae\u8425\u9500\u73ed\u7ea7\u300b\u5e2e\u780d\u4ef722","about":"<p>\u545c\u545c\u545c\u545c<\/p>","status":"ongoing","item_type":"classroom","item_source_id":"23","item_source_link":"\/classroom\/23","item_name":"\u300a\u5fae\u8425\u9500\u73ed\u7ea7\u300b","item_cover":"\/\/dev.wyx.edusoho.cn\/files\/item\/2019\/01-22\/155316cd91f6127919.png","background_picture":"","item_origin_price":"9901","item_free_video":"","item_tasks":["\u300a\u591a\u8ba1\u5212\u8bfe\u7a0b\u300b","\u8bfe\u7a0b\u300a\u780d\u4ef7\u79d2\u6740\u62fc\u56e2\u6d3b\u52a8\u8bfe\u7a0b\u300b\u7684\u6559\u5b66\u8ba1\u5212:"],"item_teachers":[],"shared_title":"\u300a\u5fae\u8425\u9500\u73ed\u7ea7\u300b\u5e2e\u780d\u4ef722","shared_picture":"item\/2019\/01-22\/155316cd91f6127919.png","shared_content":"\u545c\u545c\u545c\u545c","lowest_price":"300","start_time":"0","end_time":"0","publish_time":"1548143635","close_time":"0","order_num":"2","paid_order_num":"0","order_income":"0","hit_num":"21","is_show_merchant":"1","is_show_teacher":"1","is_show_goods_link":"1","is_show_app":"1","is_show_consult":"1","is_show_task":"0","is_set_rule":"1","updated_time":"1548143635","operator":"\u6d4b\u8bd5\u7ba1\u7406\u5458","created_time":"1548143596","product_sum":"0","product_remaind":"0","product_locked":"0","owner_price":"300","member_price":0,"rule":{"id":"111","activity_id":"566","origin_price":"9901","lowest_price":"300","reduce_amout":"9601","times":"3","average":"3200","max":"0","min":"0","background_color":"red","updated_time":"1548143607","updated_user":"52","created_time":"1548143607","created_user":"52"},"canStartActivityResult":{"success":true}}', true);
        $marketingActivityFilter = new MarketingActivityFilter();
        // $marketingActivityFilter->setMode(Filter::SIMPLE_MODE);
        $marketingActivityFilter->filter($activity);
        var_dump($activity);
        exit();
        if (in_array($data['type'], array('cut', 'seckill', 'groupon'))) {
            $marketingActivityFilter = new MarketingActivityFilter();
            $marketingActivityFilter->setMode(Filter::SIMPLE_MODE);
            $marketingActivityFilter->filter($data['data']['activity']);
        }
    }
}
