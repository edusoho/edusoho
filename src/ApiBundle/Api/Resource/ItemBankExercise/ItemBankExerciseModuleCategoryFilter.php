<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;

class ItemBankExerciseModuleCategoryFilter extends Filter
{
    protected $publicFields = [
        'id',
        'name',
        'depth',
        'weight',
        'bank_id',
        'parent_id',
        'question_count',
        'item_count',
    ];

    protected function publicFields(&$data)
    {
        // todo 临时用
        $data['question_count'] = $data['item_count'] = '0';
    }
}
