<?php

namespace ApiBundle\Api\Resource\CourseSet;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\Money;

class CourseSetFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'subtitle', 'summary', 'type', 'cover', 'studentNum', 'maxCoursePrice', 'minCoursePrice', 'discount', 'discountType', 'maxOriginPrice', 'minOriginPrice', 'status',
    );

    protected $publicFields = array(
        'tags', 'category', 'serializeMode', 'goals', 'audiences', 'ratingNum', 'rating', 'noteNum',
        'recommended', 'recommendedSeq', 'recommendedTime', 'orgId', 'orgCode', 'discountId', 'discount', 'discountType', 'maxRate', 'hitNum', 'materialNum', 'parentId', 'locked', 'maxCoursePrice', 'minCoursePrice', 'teachers', 'creator', 'createdTime', 'updatedTime',
    );

    protected function simpleFields(&$data)
    {
        $data['discount'] = strval(floatval($data['discount']));

        $data['summary'] = $this->convertAbsoluteUrl($data['summary']);
        $this->transformCover($data['cover']);

        $data['minCoursePrice2'] = Money::convert($data['minCoursePrice']);
        $data['maxCoursePrice2'] = Money::convert($data['maxCoursePrice']);

        if (isset($data['maxOriginPrice'])) {
            $data['maxOriginPrice2'] = Money::convert($data['maxOriginPrice']);
        }

        if (isset($data['minOriginPrice'])) {
            $data['minOriginPrice2'] = Money::convert($data['minOriginPrice']);
        }
    }

    protected function publicFields(&$data)
    {
        $data['discount'] = strval(floatval($data['discount']));

        $data['recommendedTime'] = date('c', $data['recommendedTime']);

        $userFilter = new UserFilter();
        $userFilter->filter($data['creator']);
        $userFilter->filters($data['teachers']);
    }

    private function transformCover(&$cover)
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], 'course.png');
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], 'course.png');
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], 'course.png');
    }
}
