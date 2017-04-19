<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;

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
        $this->transformCover($data['cover']);
    }

    protected function publicFields(&$data)
    {
        $data['summary'] = $this->convertAbsoluteUrl($data['summary']);

        $data['recommendedTime'] = date('c', $data['recommendedTime']);

        $userFilter = new UserFilter();
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);
    }

    private function transformCover(&$cover)
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '':$cover['small'], 'course.png');
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '':$cover['middle'], 'course.png');
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '':$cover['large'], 'course.png');
    }
}