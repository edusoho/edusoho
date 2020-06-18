<?php

namespace ApiBundle\Api\Resource\Assessment;

use ApiBundle\Api\Resource\Filter;

class AssessmentFilter extends Filter
{
    protected $simpleFields = [
        'id', 'title', 'description', 'question_count', 'total_score',
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
        $assessment['description'] = $this->convertAbsoluteUrl($assessment['description']);
        foreach ($assessment['sections'] as &$section) {
            foreach ($section['items'] as &$item) {
                !empty($item['material']) && $item['material'] = $this->convertAbsoluteUrl($item['material']);
                !empty($item['analysis']) && $item['analysis'] = $this->convertAbsoluteUrl($item['analysis']);
                foreach ($item['questions'] as &$question) {
                    !empty($question['stem']) && $question['stem'] = $this->convertAbsoluteUrl($question['stem']);
                    !empty($question['analysis']) && $question['analysis'] = $this->convertAbsoluteUrl($question['analysis']);
                    empty($question['response_points']) && $question['response_points'] = [];
                    foreach ($question['response_points'] as &$point) {
                        !empty($point['checkbox']['text']) && $point['checkbox']['text'] = $this->convertAbsoluteUrl($point['checkbox']['text']);
                        !empty($point['radio']['text']) && $point['radio']['text'] = $this->convertAbsoluteUrl($point['radio']['text']);
                    }
                    foreach ($question['answer'] as &$answer) {
                        !empty($answer) && $answer = $this->convertAbsoluteUrl($answer);
                    }
                }
            }
        }
    }

    protected function simpleFields(&$assessment)
    {
        $assessment['description'] = $this->convertAbsoluteUrl($assessment['description']);
    }
}
