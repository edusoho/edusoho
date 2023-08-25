<?php
namespace Codeages\Biz\ItemBank\Answer\Dao;

interface AnswerReviewedQuestionDao
{
    public function findByAnswerRecordId($answerRecordId);

    public function countByAnswerRecordId($answerRecordId);

    public function getByAnswerRecordIdAndQuestionId($recordId, $questionId);
}
