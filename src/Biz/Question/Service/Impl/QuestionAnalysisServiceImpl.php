<?php

namespace Biz\Question\Service\Impl;

use Biz\BaseService;
use Biz\Question\Service\QuestionAnalysisService;

class QuestionAnalysisServiceImpl extends BaseService implements QuestionAnalysisService
{
    public function waveCount($id, $diffs)
    {
        return $this->getQuestionAnalysisDao()->wave(array($id), $diffs);
    }

    public function searchAnalysis($conditions, $orderBys, $start, $limit)
    {
        return $this->getQuestionAnalysisDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function countAnalysis($conditions)
    {
        return $this->getQuestionAnalysisDao()->count($conditions);
    }

    public function batchCreate($rows)
    {
        return $this->getQuestionAnalysisDao()->batchCreate($rows);
    }

    protected function getQuestionAnalysisDao()
    {
        return $this->createDao('Question:QuestionAnalysisDao');
    }
}
