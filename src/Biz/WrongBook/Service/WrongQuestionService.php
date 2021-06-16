<?php

namespace Biz\WrongBook\Service;

interface WrongQuestionService
{
    public function buildWrongQuestion($fields, $source);

    public function createWrongQuestion($fields);

    public function searchWrongQuestion($conditions, $orderBys, $start, $limit);

    public function deleteWrongQuestion($id);

    public function batchBuildWrongQuestion($wrongAnswerQuestionReports, $source);
}
