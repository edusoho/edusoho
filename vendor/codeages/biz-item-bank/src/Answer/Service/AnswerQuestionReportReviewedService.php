<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerQuestionReportReviewedService
{
    public function findByAnswerRecordId($answerRecordId);

    public function countByAnswerRecordId($answerRecordId);

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId);

    public function createAnswerQuestionReportReviewed($AnswerQuestionReportReviewed);
}
