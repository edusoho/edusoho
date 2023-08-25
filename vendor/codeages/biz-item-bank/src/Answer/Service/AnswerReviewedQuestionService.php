<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerReviewedQuestionService
{
    public function findByAnswerRecordId($answerRecordId);

    public function countByAnswerRecordId($answerRecordId);

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId);

    public function createAnswerQuestionReportReviewed($AnswerQuestionReportReviewed);
}
