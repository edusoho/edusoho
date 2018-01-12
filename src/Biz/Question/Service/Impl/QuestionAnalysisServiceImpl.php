<?php

namespace Biz\Question\Service\Impl;

use Biz\BaseService;
use AppBundle\Common\ArrayToolkit;
use Biz\Question\Service\QuestionAnalysisService;

class QuestionAnalysisServiceImpl extends BaseService implements QuestionAnalysisService
{
    public function getAnalysis($id)
    {
        return $this->getQuestionAnalysisDao()->get($id);
    }

    public function getQuesionAnalysisItem($targetId, $targetType, $questionId, $choiceIndex)
    {
        return $this->getQuestionAnalysisDao()->getAnalysisItem($targetId, $targetType, $questionId, $choiceIndex);
    }

    public function findAnalysisByTargetIdAndTargetType($targetId, $targetType)
    {
        return $this->getQuestionAnalysisDao()->findByTargetIdAndTargetType($targetId, $targetType);
    }

    public function findQuestionsAnalysisFormate($targetId, $targetType, $questionIds)
    {
        $analysisFormate = array();

        if (empty($questionIds)) {
            return array();
        }

        foreach ($questionIds as $key => $questionId) {
            $quesionAnalysis = $this->getQuestionAnalysisDao()->findByTargetIdAndTargetTypeAndQuestionId($targetId, $targetType, $questionId);
            $quesionAnalysis = ArrayToolkit::index($quesionAnalysis, 'choiceIndex');

            $analysisFormate[$questionId] = $quesionAnalysis;
        }

        return $analysisFormate;
    }

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

    public function updateAnalysis($id, $fields)
    {
        return $this->getQuestionAnalysisDao()->update($id, $fields);
    }

    public function deleteAnalysis($id)
    {
        return $this->getQuestionAnalysisDao()->delete($id);
    }

    protected function getQuestionAnalysisDao()
    {
        return $this->createDao('Question:QuestionAnalysisDao');
    }
}
