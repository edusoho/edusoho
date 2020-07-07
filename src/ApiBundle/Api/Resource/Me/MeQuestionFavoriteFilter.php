<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\Resource\Assessment\AssessmentFilter;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Item\QuestionFilter;

class MeQuestionFavoriteFilter extends Filter
{
    protected $publicFields = [
        'id', 'target_type', 'target_id', 'item_id', 'user_id', 'created_time', 'question', 'assessment',
    ];

    protected function publicFields(&$data)
    {
        if (!empty($data['assessment'])) {
            $assessmentFilter = new AssessmentFilter();
            $assessmentFilter->setMode(Filter::SIMPLE_MODE);
            $assessmentFilter->filter($data['assessment']);
        }

        if (!empty($data['question'])) {
            $questionFilter = new QuestionFilter();
            $questionFilter->filter($data['question']);
        }
    }
}
