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
        $data['displayMaterial'] = str_replace('[[]]', '___', html_entity_decode(strip_tags($data['material'])));
    }
}
