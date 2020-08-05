<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Filter;

class ItemBankExerciseModuleFilter extends Filter
{
    protected $publicFields = [
        'id',
        'seq',
        'exerciseId',
        'answerSceneId',
        'title',
        'type',
        'createdTime',
        'updatedTime',
    ];

    protected function publicFields(&$data)
    {
    }
}
