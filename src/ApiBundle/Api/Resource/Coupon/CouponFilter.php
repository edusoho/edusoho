<?php

namespace ApiBundle\Api\Resource\Coupon;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\CourseSet\CourseSetFilter;
use ApiBundle\Api\Resource\Classroom\ClassroomFilter;
use VipPlugin\Api\Resource\VipLevel\VipLevelFilter;

class CouponFilter extends Filter
{
    protected $publicFields = array(
        'id', 'code', 'type', 'status', 'rate', 'userId', 'deadline', 'targetType', 'targetId', 'target', 'targetDetail',
    );

    protected function publicFields(&$data)
    {
        $data['deadline'] = date('c', $data['deadline']);

        if ('discount' == $data['type']) {
            $data['rate'] = strval(floatval($data['rate']));
        }

        if (!empty($data['target'])) {
            $targetFilter = $this->getFilter($data['targetType']);
            $targetFilter->setMode(Filter::SIMPLE_MODE);
            $targetFilter->filter($data['target']);
        }
        if (!empty($data['targetDetail']) && !empty($data['targetDetail']['data'])) {
            foreach ($data['targetDetail']['data'] as &$target) {
                $targetFilter = $this->getFilter($data['targetType']);
                $targetFilter->setMode(Filter::SIMPLE_MODE);
                $targetFilter->filter($target);
            }
        }

        isset($data['target']) && is_array($data['target']) ? $data['targetId'] = $data['target']['id'] : $data['target'] = null;
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
