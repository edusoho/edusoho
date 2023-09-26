<?php

namespace ApiBundle\Api\Resource\Exercise;

use ApiBundle\Api\Resource\Filter;

class ExerciseFilter extends Filter
{
    protected $publicFields = [
        'id', 'name', 'itemCount', 'latestExerciseResult', 'createdTime', 'updatedTime', 'lastExerciseResult', 'itemCounts',
    ];

    protected function publicFields(&$data)
    {
        if (isset($data['latestExerciseResult'])) {
            $exerciseResultFilter = new ExerciseResultFilter();
            $exerciseResultFilter->setMode(Filter::SIMPLE_MODE);
            $exerciseResultFilter->filter($data['latestExerciseResult']);
        }

        if (isset($data['lastExerciseResult'])) {
            $exerciseResultFilter = new ExerciseResultFilter();
            $exerciseResultFilter->setMode(Filter::SIMPLE_MODE);
            $exerciseResultFilter->filter($data['lastExerciseResult']);
        }
    }
}
