<?php

namespace ApiBundle\Api\Resource\Item;

use ApiBundle\Api\Resource\Filter;

class QuestionFilter extends Filter
{
    protected $publicFields = [
        'id',
        'item_id',
        'stem',
        'seq',
        'score',
        'answer_mode',
        'response_points',
        'answer',
        'analysis',
        'attachments',
        'isDelete',
    ];

    protected function publicFields(&$question)
    {
        !empty($question['stem']) && $question['stem'] = $this->convertAbsoluteUrl($question['stem']);
        !empty($question['analysis']) && $question['analysis'] = $this->convertAbsoluteUrl($question['analysis']);
        empty($question['response_points']) && $question['response_points'] = [];
        empty($question['analysis']) && $question['analysis'] = '';
        foreach ($question['response_points'] as &$point) {
            !empty($point['checkbox']['text']) && $point['checkbox']['text'] = $this->convertAbsoluteUrl($point['checkbox']['text']);
            !empty($point['radio']['text']) && $point['radio']['text'] = $this->convertAbsoluteUrl($point['radio']['text']);
        }
        if (!empty($question['answer'])) {
            foreach ($question['answer'] as &$answer) {
                !empty($answer) && $answer = $this->convertAbsoluteUrl($answer);
            }
        }
    }
}
