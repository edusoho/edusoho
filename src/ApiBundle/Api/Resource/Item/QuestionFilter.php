<?php

namespace ApiBundle\Api\Resource\Item;

use ApiBundle\Api\Resource\Filter;
use Biz\Question\Traits\QuestionFormulaImgTrait;

class QuestionFilter extends Filter
{
    use QuestionFormulaImgTrait;

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
        $question = $this->convertFormulaToImg($question);
        !empty($question['stem']) && $question['stem'] = $this->convertAbsoluteUrl($question['stem']);
        !empty($question['analysis']) && $question['analysis'] = $this->convertAbsoluteUrl($question['analysis']);
        empty($question['analysis']) && $question['analysis'] = '';
        empty($question['response_points']) && $question['response_points'] = [];

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
