<?php

namespace MarketingMallBundle\Api\Resource;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class BaseFilter extends Filter
{
    public function filters(&$dataSet)
    {
        if (!$dataSet || !is_array($dataSet)) {
            return;
        }
        if (array_key_exists('data', $dataSet) && array_key_exists('page', $dataSet)) {
            foreach ($dataSet['data'] as &$data) {
                $this->filter($data);
            }
        } else {
            foreach ($dataSet as &$data) {
                $this->filter($data);
            }
        }
    }

    public function transformCover($cover, $default = 'course.png')
    {
        $cover['small'] = AssetHelper::getFurl(empty($cover['small']) ? '' : $cover['small'], $default);
        $cover['middle'] = AssetHelper::getFurl(empty($cover['middle']) ? '' : $cover['middle'], $default);
        $cover['large'] = AssetHelper::getFurl(empty($cover['large']) ? '' : $cover['large'], $default);

        return $cover;
    }
}
