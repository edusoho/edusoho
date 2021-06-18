<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\Resource\Filter;

class WrongBookCertainTypeFilter extends Filter
{
    protected $publicFields = [
        'id',
        'user_id',
        'item_num',
        'target_type',
        'created_time',
        'updated_time',
        'target_data',
    ];
}
