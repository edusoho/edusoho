<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerRandomSeqService
{
    public function createAnswerRandomSeqRecordIfNecessary($answerRecordId);

    public function shuffleItemsAndOptionsIfNecessary($assessment, $answerRecordId);

    public function restoreOptionsToOriginalSeqIfNecessary($assessmentResponse);

    public function shuffleQuestionReportsAndConvertOptionsIfNecessary($questionReports, $answerRecordId);
}
