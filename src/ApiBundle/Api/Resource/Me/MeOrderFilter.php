<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class MeOrderFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'title', 'sn', 'pay_amount', 'created_time', 'status', 'cover', 'targetType', 'targetId',
    );

    protected function simpleFields(&$data)
    {
        if (0 === strpos($data['cover']['middle'], '/assets') && '' !== $data['cover']) {
            $data['cover']['small'] = AssetHelper::uriForPath($data['cover']['small']);
            $data['cover']['middle'] = AssetHelper::uriForPath($data['cover']['middle']);
            $data['cover']['large'] = AssetHelper::uriForPath($data['cover']['large']);
        } else {
            // VipProduct中的图片，是以 /files 开头的
            if ('vip' == $data['targetType'] && !empty($data['cover']['small'])) {
                $data['cover']['small'] = AssetHelper::uriForPath($data['cover']['small']);
                $data['cover']['middle'] = AssetHelper::uriForPath($data['cover']['middle']);
                $data['cover']['large'] = AssetHelper::uriForPath($data['cover']['large']);
            } else {
                $targetTypeIcon = ('vip' == $data['targetType']) ? 'vip_icon_bronze' : $data['targetType'];
                $data['cover']['small'] = AssetHelper::getFurl(empty($data['cover']['small']) ? '' : $data['cover']['small'], $targetTypeIcon.'.png');
                $data['cover']['middle'] = AssetHelper::getFurl(empty($data['cover']['middle']) ? '' : $data['cover']['middle'], $targetTypeIcon.'.png');
                $data['cover']['large'] = AssetHelper::getFurl(empty($data['cover']['large']) ? '' : $data['cover']['large'], $targetTypeIcon.'.png');
            }
        }
    }
}
