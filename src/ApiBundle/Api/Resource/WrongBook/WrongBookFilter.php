<?php

namespace ApiBundle\Api\Resource\WrongBook;

use ApiBundle\Api\Resource\Filter;

class WrongBookFilter extends Filter
{
    protected $publicFields = [
        'user_id',
        'sum_wrong_num',
        'target_type',
        'wrongNumCount',
    ];

    protected function publicFields(&$data)
    {
        if (isset($data['sum_wrong_num'])) {
            $data['sum_wrong_num'] = (int) $data['sum_wrong_num'];
        }

        if (isset($data['wrongNumCount'])) {
            $data['wrongNumCount'] = (int) $data['wrongNumCount'];
        }
    }
}
