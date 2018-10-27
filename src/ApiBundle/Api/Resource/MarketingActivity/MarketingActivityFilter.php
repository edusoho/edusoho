<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;
use Biz\Marketing\Util\MarketingUtils;

class MarketingActivityFilter extends Filter
{
    protected $publicFields = array(
        'id', 'name', 'type', 'about', 'status', 'item_origin_price', 'owner_price', 'member_price', 'shared_picture', 'created_time', 'start_time', 'end_time',
    );

    protected function publicFields(&$data)
    {
        $data['originPrice'] = $data['item_origin_price'];
        unset($data['item_origin_price']);
        if (isset($data['owner_price'])) {
            $data['ownerPrice'] = $data['owner_price'];
            unset($data['owner_price']);
        }
        if (isset($data['member_price'])) {
            $data['memberPrice'] = $data['member_price'];
            unset($data['member_price']);
        }
        $data['cover'] = $data['shared_picture'];
        unset($data['shared_picture']);
        $data['createdTime'] = Converter::timestampToDate($data['created_time']);
        unset($data['created_time']);
        $data['startTime'] = Converter::timestampToDate($data['start_time']);
        unset($data['start_time']);
        $data['endTime'] = Converter::timestampToDate($data['end_time']);
        unset($data['end_time']);
        $marketingDomain = MarketingUtils::getMarketingDomain();
        $data['url'] = $marketingDomain.'/a/groupon/show/'.$data['id'];
    }
}
