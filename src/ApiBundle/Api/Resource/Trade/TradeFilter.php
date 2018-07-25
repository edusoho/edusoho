<?php

namespace ApiBundle\Api\Resource\Trade;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;

class TradeFilter extends Filter
{
    protected $simpleFields = array(
        'tradeSn', 'isPaid', 'paidSuccessUrl',
    );

    protected $publicFields = array(
        'status', 'payUrl', 'paymentForm', 'paymentHtml', 'paymentUrl',
    );

    protected function publicFields(&$data)
    {
        if (!empty($data['payUrl'])) {
            $data['payUrl'] = AssetHelper::uriForPath($data['payUrl']);
        }
    }
}