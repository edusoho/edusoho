<?php

namespace Codeages\Biz\ItemBank\AnswerQuestionTag\Service;

interface AnswerQuestionTagService
{
    public function createAnswerQuestionTag($answerQuestionTag);

    public function updateAnswerQuestionTag($id, $answerQuestionTag);

    public function getByAnswerRecordId($answerRecordId);
    
    public function deleteAnswerQuestionTag($id);
}