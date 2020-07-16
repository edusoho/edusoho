<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\Money;

class GoodFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'subtitle', 'summary', 'minPrice', 'maxPrice', 'images', 'ratingNum', 'rating', 'hitNum',
        'hotSeq', 'showable', 'buyable', 'creator',
    ];

    protected $publicFields = [
        'id', 'title', 'subtitle', 'product', 'extensions', 'specs', 'creator', 'status',
        'showable', 'buyable', 'summary', 'minPrice', 'maxPrice', 'images', 'orgId', 'orgCode', 'ratingNum', 'rating',
        'hitNum', 'hotSeq', 'recommendWeight', 'recommendedTime', 'createdTime', 'updatedTime', 'isFavorite',
    ];

    protected $filterMap = [
        'course' => 'ApiBundle\Api\Resource\CourseSet\CourseSetFilter',
        'classroom' => 'ApiBundle\Api\Resource\Classroom\ClassroomFilter',
    ];

    protected function simpleFields(&$data)
    {
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['creator']);

        $data['summary'] = $this->convertAbsoluteUrl($data['summary']);
        $this->transformImages($data['images']);
        $this->transMinAndMaxPrice($data);
    }

    protected function publicFields(&$data)
    {
        $data['summary'] = $this->convertAbsoluteUrl($data['summary']);
        $goodSpecsFilter = new GoodSpecsFilter();
        $goodSpecsFilter->setMode(Filter::SIMPLE_MODE);
        $goodSpecsFilter->filters($data['specs']);

        if (!empty($data['product']) && !empty($data['product']['target']) && in_array($data['product']['targetType'], array_keys($this->filterMap))) {
            $class = $this->filterMap[$data['product']['targetType']];
            $targetFilter = new $class();
            $targetFilter->setMode(Filter::SIMPLE_MODE);
            $targetFilter->filter($data['product']['target']);
        }

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($data['creator']);

        $this->transformImages($data['images']);
        $this->transMinAndMaxPrice($data);
    }

    private function transMinAndMaxPrice(&$data)
    {
        $data['minPrice'] = Money::convert($data['minPrice']);
        $data['maxPrice'] = Money::convert($data['maxPrice']);
    }

    private function transformImages(&$images)
    {
        $images['small'] = AssetHelper::getFurl(empty($images['small']) ? '' : $images['small'], 'course.png');
        $images['middle'] = AssetHelper::getFurl(empty($images['middle']) ? '' : $images['middle'], 'course.png');
        $images['large'] = AssetHelper::getFurl(empty($images['large']) ? '' : $images['large'], 'course.png');
    }
}
