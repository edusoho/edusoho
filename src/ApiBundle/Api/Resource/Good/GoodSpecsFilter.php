<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use AppBundle\Common\ServiceToolkit;

class GoodSpecsFilter extends Filter
{
    protected $simpleFields = [
        'id', 'goodsId', 'targetId', 'title', 'seq', 'status', 'price', 'coinPrice', 'buyableStartTime', 'buyableEndTime', 'buyableMode', 'maxJoinNum', 'services',
    ];

    protected $publicFields = [
        'id', 'goodsId', 'targetId', 'title', 'images', 'seq', 'status', 'price', 'coinPrice', 'buyableStartTime', 'buyableEndTime', 'buyableMode', 'maxJoinNum', 'services',
    ];

    protected function simpleFields(&$data)
    {
        $this->transServices($data['services']);
    }

    protected function publicFields(&$data)
    {
        $this->transServices($data['services']);

        $this->transformImages($data['images']);
    }

    private function transServices(&$services)
    {
        if (empty($services)) {
            return $services;
        }
        $services = AssetHelper::callAppExtensionMethod('transServiceTags', [ServiceToolkit::getServicesByCodes($services)]);
    }

    private function transformImages(&$images)
    {
        $images['small'] = AssetHelper::getFurl(empty($images['small']) ? '' : $images['small'], 'course.png');
        $images['middle'] = AssetHelper::getFurl(empty($images['middle']) ? '' : $images['middle'], 'course.png');
        $images['large'] = AssetHelper::getFurl(empty($images['large']) ? '' : $images['large'], 'course.png');
    }
}
