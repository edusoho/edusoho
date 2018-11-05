<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;
use Biz\Marketing\Util\MarketingUtils;

class MarketingActivityFilter extends Filter
{
    protected $publicFields = array(
        'id', 'name', 'type', 'about', 'status', 'item_origin_price', 'owner_price', 'member_price', 'item_cover', 'created_time', 'start_time', 'end_time',
    );

    protected function publicFields(&$data)
    {
        $data['originPrice'] = $data['item_origin_price'] / 100;
        unset($data['item_origin_price']);
        if (isset($data['owner_price'])) {
            $data['ownerPrice'] = $data['owner_price'] / 100;
            unset($data['owner_price']);
        }
        if (isset($data['member_price'])) {
            $data['memberPrice'] = $data['member_price'] / 100;
            unset($data['member_price']);
        }
        $data['cover'] = $data['item_cover'];
        unset($data['item_cover']);
        Converter::timestampToDate($data['created_time']);
        $data['createdTime'] = $data['created_time'];
        unset($data['created_time']);
        Converter::timestampToDate($data['start_time']);
        $data['startTime'] = $data['start_time'];
        unset($data['start_time']);
        Converter::timestampToDate($data['end_time']);
        $data['endTime'] = $data['end_time'];
        unset($data['end_time']);
        $marketingDomain = MarketingUtils::getMarketingDomain();
        $data['url'] = $marketingDomain.'/h5/a/groupon/show/'.$data['id'];
    }
}
