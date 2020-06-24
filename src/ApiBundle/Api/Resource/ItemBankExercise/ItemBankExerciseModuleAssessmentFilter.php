<?php

namespace ApiBundle\Api\Resource\ItemBankExercise;

use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use ApiBundle\Api\Resource\Filter;
use AppBundle\Common\ArrayToolkit;

class ItemBankExerciseModuleAssessmentFilter extends Filter
{
    protected $publicFields = [
        'id',
        'exerciseId',
        'moduleId',
        'assessment',
        'latestAnswerRecord',
    ];

    protected function publicFields(&$data)
    {
        $assessmentFilter = new AssessmentFilter();
        $assessmentFilter->setMode(Filter::SIMPLE_MODE);
        $assessmentFilter->filter($data['assessment']);

        if (!empty($data['latestAnswerRecord'])) {
            $data['latestAnswerRecord'] = ArrayToolkit::parts($data['latestAnswerRecord'], ['id', 'status', 'answerRecordId']);
        }
    }
}
