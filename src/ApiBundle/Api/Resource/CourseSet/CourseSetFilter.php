<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\RequestUtil;
use AppBundle\Common\ArrayToolkit;

class CourseSetFilter extends Filter
{
    protected $publicFields = array(
        'id', 'title', 'subtitle', 'type', 'tags', 'category', 'serializeMode', 'status',
        'summary', 'goals', 'audiences', 'cover', 'ratingNum', 'rating', 'noteNum', 'studentNum',
        'recommended', 'recommendedSeq', 'recommendedTime', 'orgId', 'orgCode', 'discountId',
        'discount', 'maxRate', 'hitNum', 'materialNum', 'parentId', 'locked', 'maxCoursePrice',
        'minCoursePrice', 'teachers', 'creator', 'createdTime', 'updatedTime'
    );

    protected function customFilter(&$data)
    {
        $data['recommendedTime'] = date('c', $data['recommendedTime']);

        foreach ($data['cover'] as $size => $imagePath) {
            $data['cover'][$size] = RequestUtil::asset($imagePath);
        }

        $userFilter = new UserFilter();
        $userFilter->filter($data['creator']);
    }
}