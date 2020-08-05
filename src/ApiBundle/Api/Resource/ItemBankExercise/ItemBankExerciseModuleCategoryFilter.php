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
        'item_num',
        'question_num',
        'latestAnswerRecord',
    ];

    protected function publicFields(&$data)
    {
        if (!empty($data['latestAnswerRecord'])) {
            $itemBankChapterExerciseRecordFilter = new ItemBankChapterExerciseRecordFilter();
            $itemBankChapterExerciseRecordFilter->filter($data['latestAnswerRecord']);
        }
    }
}
