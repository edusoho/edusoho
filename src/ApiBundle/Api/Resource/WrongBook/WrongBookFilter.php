<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Util\AssetHelper;
use ApiBundle\Api\Util\TagUtil;

class WrongBookFilter extends Filter
{
    protected $publicFields = array(
        'user_id',
        'sum_wrong_num',
        'target_type',
    );

    protected function publicFields(&$data)
    {
        $data['sum_wrong_num']=(int)$data['sum_wrong_num'];
    }
}
