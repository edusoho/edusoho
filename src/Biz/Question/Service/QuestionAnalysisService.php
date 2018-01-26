<?php

namespace Biz\Question\Service;

interface QuestionAnalysisService
{
    public function getAnalysis($id);

    public function getQuesionAnalysisItem($targetId, $targetType, $questionId, $choiceIndex);

    public function findAnalysisByTargetIdAndTargetType($targetId, $targetType);

    public function findQuestionsAnalysisFormate($targetId, $targetType, $questionIds);

    public function waveCount($id, $diffs);

    public function searchAnalysis($conditions, $orderBys, $start, $limit);

    public function countAnalysis($conditions);

    public function batchCreate($rows);

    public function updateAnalysis($id, $fields);

    public function deleteAnalysis($id);
}
