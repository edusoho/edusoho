<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;

class ItemBankChapterExerciseRecordFilter extends Filter
{
    protected $publicFields = [
        'id', 'moduleId', 'exerciseId', 'itemCategoryId', 'status', 'rightRate', 'questionNum', 'answerRecordId', 'doneQuestionNum',
    ];

    protected function publicFields(&$data)
    {
    }
}
