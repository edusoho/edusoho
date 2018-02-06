<?php

namespace Biz\Question\Service;

interface QuestionAnalysisService
{
    public function waveCount($id, $diffs);

    public function searchAnalysis($conditions, $orderBys, $start, $limit);

    public function countAnalysis($conditions);

    public function batchCreate($rows);
}
