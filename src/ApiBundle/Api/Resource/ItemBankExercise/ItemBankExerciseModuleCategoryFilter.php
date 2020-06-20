<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;
use AppBundle\Common\ArrayToolkit;

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
            $data['latestAnswerRecord'] = ArrayToolkit::parts($data['latestAnswerRecord'], ['id', 'status', 'rightRate', 'questionNum', 'answerRecordId', 'doneQuestionNum']);
        }
    }
}
