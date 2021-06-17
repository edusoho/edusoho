<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\TagUtil;

class WrongBookFilter extends Filter
{
    protected $authenticatedFields = array(
        'user_id',
        'sum_wrong_num',
        'target_type',
    );
}
