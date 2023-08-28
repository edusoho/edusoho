<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerReviewedQuestionService
{
    public function findByAnswerRecordId($answerRecordId);

    public function countReviewedByAnswerRecordId($answerRecordId);

    public function getByAnswerRecordIdAndQuestionId($answerRecordId, $questionId);

    public function createAnswerReviewedQuestion($answerReviewedQuestion);

    public function updateAnswerReviewedQuestion($id, $params);
}
