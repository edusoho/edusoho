<?php

namespace ApiBundle\Api\Resource\InformationCollectEvent;

use ApiBundle\Api\Resource\Filter;

class InformationCollectEventFilter extends Filter
{
    protected $publicFields = [
        'id',
        'title',
        'action',
        'formTitle',
        'status',
        'allowSkip',
        'creator',
        'isSubmited',
        'createdTime',
        'updatedTime',
    ];
}
