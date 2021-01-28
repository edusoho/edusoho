<?php

namespace ApiBundle\Api\Resource\MarketingActivity;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\Converter;
use Biz\Marketing\Util\MarketingUtils;

class MarketingActivityFilter extends Filter
{
    protected $simpleFields = array(
        'id', 'name', 'type', 'status', 'item_origin_price', 'item_type', 'item_source_id', 'order', 'rule', 'item_cover', 'product_remaind', 'created_time', 'start_time', 'end_time',
    );

    protected $publicFields = array(
        'about',
    );

    protected function simpleFields(&$data)
    {
        $data['originPrice'] = $data['item_origin_price'] / 100;
        unset($data['item_origin_price']);

        $data['itemType'] = $data['item_type'];
        unset($data['item_type']);

        $data['itemSourceId'] = $data['item_source_id'];
        unset($data['item_source_id']);

        $data['cover'] = $data['item_cover'];
        unset($data['item_cover']);

        $data['productRemaind'] = $data['product_remaind'];
        unset($data['product_remaind']);

        Converter::timestampToDate($data['created_time']);
        $data['createdTime'] = $data['created_time'];
        unset($data['created_time']);

        Converter::timestampToDate($data['start_time']);
        $data['startTime'] = $data['start_time'];
        unset($data['start_time']);

        Converter::timestampToDate($data['end_time']);
        $data['endTime'] = $data['end_time'];
        unset($data['end_time']);

        if (isset($data['groupon_id'])) {
            $data['grouponId'] = $data['groupon_id'];
            unset($data['groupon_id']);
        }

        $marketingDomain = MarketingUtils::getMarketingDomain();
        $data['url'] = $marketingDomain.'/h5/a/'.$data['type'].'/show/'.$data['id'];

        if (isset($data['rule']) && in_array($data['type'], array('seckill', 'groupon', 'cut'))) {
            $maketingActivityRuleFilter = 'ApiBundle\Api\Resource\MarketingActivity\MaketingActivity'.ucfirst($data['type']).'RuleFilter';
            $maketingActivityRuleFilter = new $maketingActivityRuleFilter();
            $maketingActivityRuleFilter->filter($data['rule']);
        }

        if (isset($data['order'])) {
            $data['payAmount'] = $data['order']['pay_amount'] / 100;
            unset($data['order']);
        }
    }
}
