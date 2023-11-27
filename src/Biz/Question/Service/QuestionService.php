<?php

namespace Biz\Question\Service;

interface QuestionService
{
    public function createQuestionFormulaImgRecords(array $questionFormulaImgRecords);

    public function findQuestionFormulaImgRecordsByFormulas(array $formulas);
}
