<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseFilter;

class MeItemBankExerciseFilter extends Filter
{
    protected $publicFields = [
        'id', 'exerciseId', 'questionBankId', 'doneQuestionNum', 'doneAssessmentNum', 'questionNum', 'masteryRate', 'itemBankExercise',
    ];

    protected function publicFields(&$data)
    {
        $itemBankExerciseFilter = new ItemBankExerciseFilter();
        $itemBankExerciseFilter->setMode(Filter::SIMPLE_MODE);
        $itemBankExerciseFilter->filter($data['itemBankExercise']);
    }
}
