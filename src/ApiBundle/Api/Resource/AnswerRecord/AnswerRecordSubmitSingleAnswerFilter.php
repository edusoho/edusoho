<?php

namespace ApiBundle\Api\Resource\AnswerRecord;

use ApiBundle\Api\Resource\Filter;
use Biz\Question\Traits\QuestionFormulaImgTrait;

class AnswerRecordSubmitSingleAnswerFilter extends Filter
{
    use QuestionFormulaImgTrait;

    protected $publicFields = [
        'response',
        'answer',
        'questionId',
        'itemAnalysis',
        'questionAnalysis',
        'status',
        'manualMarking',
        'reviewedCount',
        'totalCount',
        'isAnswerFinished',
    ];

    protected function publicFields(&$result)
    {
        $result = $this->convertFormulaToImg($result);
        $result['itemAnalysis'] = $this->convertAbsoluteUrl($result['itemAnalysis']);
        $result['questionAnalysis'] = $this->convertAbsoluteUrl($result['questionAnalysis']);
    }
}
