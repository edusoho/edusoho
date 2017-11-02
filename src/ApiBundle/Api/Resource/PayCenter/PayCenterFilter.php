<?php

namespace ApiBundle\Api\Resource\PayCenter;

use ApiBundle\Api\Resource\Filter;

class PayCenterFilter extends Filter
{
    protected $publicFields = array(
        'id', 'trade_sn', 'status', 'paymentForm', 'paymentHtml'
    );

    protected function publicFields(&$data)
    {
        $data['sn'] = $data['trade_sn'];
    }
}