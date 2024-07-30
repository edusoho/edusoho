<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Item\ItemFilter;
use ApiBundle\Api\Resource\User\UserFilter;

class AssessmentFilter extends Filter
{
    protected $simpleFields = [
        'id', 'name', 'description', 'question_count', 'total_score', 'assessmentGenerateRule',
    ];

    protected $publicFields = [
        'id',
        'bank_id',
        'displayable',
        'name',
        'description',
        'total_score',
        'status',
        'item_count',
        'question_count',
        'created_user_id',
        'updated_user_id',
        'created_time',
        'updated_time',
        'sections',
        'type',
        'updated_user',
        'num',
    ];

    protected function publicFields(&$assessment)
    {
        $itemFilter = new ItemFilter();
        $assessment['description'] = $this->convertAbsoluteUrl($assessment['description']);
        if (!empty($assessment['sections'])) {
            foreach ($assessment['sections'] as &$section) {
                $itemFilter->filters($section['items']);
            }
        }

        $userFilter = new UserFilter();
        $userFilter->setMode(Filter::SIMPLE_MODE);
        $userFilter->filter($assessment['created_user']);
    }

    protected function simpleFields(&$assessment)
    {
        $assessment['description'] = $this->convertAbsoluteUrl($assessment['description']);
    }
}
