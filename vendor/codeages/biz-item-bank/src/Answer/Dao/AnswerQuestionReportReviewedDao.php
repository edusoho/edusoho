<?php
namespace Codeages\Biz\ItemBank\Answer\Dao;

interface AnswerQuestionReportReviewedDao
{
    public function findByAnswerRecordId($answerRecordId);

    public function countByAnswerRecordId($answerRecordId);

    public function getByAnswerRecordIdAndQuestionId($recordId, $questionId);
}
