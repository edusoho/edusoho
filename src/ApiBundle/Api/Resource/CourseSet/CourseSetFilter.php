<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\RequestUtil;

class CourseSetFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'subtitle', 'type', 'cover', 'studentNum', 'maxCoursePrice', 'minCoursePrice', 'discount'
    );

    protected $publicFields = array(
        'tags', 'category', 'serializeMode', 'status', 'summary', 'goals', 'audiences', 'ratingNum', 'rating', 'noteNum',
        'recommended', 'recommendedSeq', 'recommendedTime', 'orgId', 'orgCode', 'discountId',
        'discount', 'maxRate', 'hitNum', 'materialNum', 'parentId', 'locked', 'maxCoursePrice', 'minCoursePrice', 'teachers', 'creator', 'createdTime', 'updatedTime'
    );

    protected function simpleFields(&$data)
    {
        foreach ($data['cover'] as $size => $imagePath) {
            $data['cover'][$size] = RequestUtil::getUriForPath($imagePath);
        }

        if (empty($data['cover'])) {
            $data['cover'] = new \stdClass();
        }
    }

    protected function publicFields(&$data)
    {
        $data['summary'] = $this->convertAbsoluteUrl($data['summary']);

        $data['recommendedTime'] = date('c', $data['recommendedTime']);

        $userFilter = new UserFilter();
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);
    }
}