<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\Item\ItemFilter;

class AssessmentFilter extends Filter
{
    protected $simpleFields = [
        'id', 'name', 'description', 'question_count', 'total_score',
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
    ];

    protected function publicFields(&$assessment)
    {
        $itemFilter = new ItemFilter();
        $assessment['description'] = $this->convertAbsoluteUrl($assessment['description']);
        foreach ($assessment['sections'] as &$section) {
            $itemFilter->filters($section['items']);
        }
    }

    protected function simpleFields(&$assessment)
    {
        $assessment['description'] = $this->convertAbsoluteUrl($assessment['description']);
    }
}
