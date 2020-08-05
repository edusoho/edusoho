<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;

class ItemBankAssessmentExerciseRecordFilter extends Filter
{
    protected $publicFields = [
        'id', 'exerciseId', 'moduleId', 'assessmentId', 'userId', 'answerRecordId', 'status', 'createdTime', 'updatedTime',
    ];

    protected function publicFields(&$data)
    {
    }
}
