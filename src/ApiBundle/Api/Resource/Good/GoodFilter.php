<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\ServiceToolkit;

class GoodFilter extends Filter
{
    protected $publicFields = [
        'id', 'title', 'subtitle', 'description', 'product', 'extensions', 'specs', 'creator', 'status',
        'showable', 'buyable', 'summary', 'minPrice', 'maxPrice', 'images', 'orgId', 'orgCode', 'ratingNum', 'rating',
        'hitNum', 'hotSeq', 'recommendWeight', 'recommendedTime', 'createdTime', 'updatedTime', 'isFavorite',
    ];

    protected $filterMap = [
        'course' => 'ApiBundle\Api\Resource\CourseSet\CourseSetFilter',
        'classroom' => 'ApiBundle\Api\Resource\Classroom\ClassroomFilter',
    ];

    protected function publicFields(&$data)
    {
        if (!empty($data['specs'])) {
            foreach ($data['specs'] as &$spec) {
                $spec['services'] = AssetHelper::callAppExtensionMethod('transServiceTags', [ServiceToolkit::getServicesByCodes($spec['services'])]);
            }
        }

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
    }

    private function transformImages(&$images)
    {
        $images['small'] = AssetHelper::getFurl(empty($images['small']) ? '' : $images['small'], 'course.png');
        $images['middle'] = AssetHelper::getFurl(empty($images['middle']) ? '' : $images['middle'], 'course.png');
        $images['large'] = AssetHelper::getFurl(empty($images['large']) ? '' : $images['large'], 'course.png');
    }
}
