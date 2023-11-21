<?php

namespace ApiBundle\Api\Resource\QuestionBank;

use ApiBundle\Api\Resource\Filter;

class QuestionBankDuplicativeMaterialFilter extends Filter
{
    protected $publicFields = [
        'material',
        'frequency',
    ];

    protected function publicFields(&$data)
    {
        $data['material'] = html_entity_decode($data['material']);
    }
}
