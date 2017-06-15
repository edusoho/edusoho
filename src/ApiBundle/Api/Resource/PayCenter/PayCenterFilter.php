<?php

namespace ApiBundle\Api\Resource\PayCenter;

use ApiBundle\Api\Resource\Filter;

class PayCenterFilter extends Filter
{
    protected $publicFields = array(
        'id', 'sn', 'status', 'paymentForm', 'paymentHtml'
    );
}