<?php

namespace ApiBundle\Api\Resource\InformationCollectForm;

use ApiBundle\Api\Resource\Filter;

class InformationCollectFormItemFilter extends Filter
{
    protected $publicFields = [
        'code',
        'labelName',
        'required',
        'data',
    ];

    protected function publicFields(&$data)
    {
        $data['required'] = (bool) $data['required'];
    }
}
