<?php

namespace ApiBundle\Api\Resource\OrderInfo;

use ApiBundle\Api\Resource\Filter;

class OrderInfoFilter extends Filter
{
    protected function customFilter(&$data)
    {
        $orderInfo = array(
            'targetId' => $data['targetId'],
            'targetType' => $data['targetType'],
            'title' => $data[$data['targetType']]['title'],
            'account' => empty($data['account']) ? array() : $data['account'],
            'hasPayPassword' => empty($data['hasPayPassword']) ? 0 : $data['hasPayPassword'],
            'verifiedMobile' => empty($data['verifiedMobile']) ? '' : $data['verifiedMobile'],
            'cashRate' => empty($data['cashRate']) ? 0 : $data['cashRate'],
            'priceType' => empty($data['priceType']) ? 'RMB' : $data['priceType'],
            'coinPayAmount' => empty($data['coinPayAmount']) ? 0 : $data['coinPayAmount'],
            'maxCoin' => empty($data['maxCoin']) ? 0: $data['maxCoin']
        );

        $data = $orderInfo;
    }
}