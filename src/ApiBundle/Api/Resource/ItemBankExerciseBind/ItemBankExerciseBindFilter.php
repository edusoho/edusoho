<?php

namespace ApiBundle\Api\Resource\ItemBankExerciseBind;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\ItemBankExercise\ItemBankExerciseFilter;
use ApiBundle\Api\Resource\User\UserFilter;

class ItemBankExerciseBindFilter extends Filter
{
    protected $publicFields = [
        'id', 'chapterExerciseNum', 'assessmentNum', 'bindType', 'bindId', 'createdTime', 'itemBankExercise', 'operateUser',
        'itemBankExerciseId', 'seq', 'updatedTime',
    ];

    protected function publicFields(&$data)
    {
        $itemBankExerciseFilter = new ItemBankExerciseFilter();
        $itemBankExerciseFilter->setMode(Filter::PUBLIC_MODE);
        $itemBankExerciseFilter->filter($data['itemBankExercise']);
        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::PUBLIC_MODE);
        $userFilter->filter($data['operateUser']);
    }
}
