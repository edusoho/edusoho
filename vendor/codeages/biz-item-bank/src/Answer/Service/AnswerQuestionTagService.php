<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerQuestionTagService
{
    public function createAnswerQuestionTag($answerRecordId, $questionIds);

    public function updateByAnswerRecordId($answerRecordId, $questionIds);

    public function deleteByAnswerRecordId($answerRecordId);

    public function getTagQuestionIdsByAnswerRecordId($answerRecordId);
}
