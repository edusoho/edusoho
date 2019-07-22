<?php

namespace ApiBundle\Api\Resource\Setting;

use ApiBundle\Api\Resource\Filter;

class CloudSmsFilter extends Filter
{
    protected $publicFields = array(
        'sms_enabled'
    );

    protected function publicFields(&$data)
    {
    }
}