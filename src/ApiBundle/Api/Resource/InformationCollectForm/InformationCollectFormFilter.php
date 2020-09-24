<?php

namespace ApiBundle\Api\Resource\InformationCollectForm;

use ApiBundle\Api\Resource\Filter;

class InformationCollectFormFilter extends Filter
{
    protected $publicFields = [
        'id',
        'formTitle',
        'allowSkip',
        'items',
    ];

    protected function publicFields(&$data)
    {
        $data['eventId'] = $data['id'];
        $data['allowSkip'] = (bool) $data['allowSkip'];
        unset($data['id']);
    }
}
