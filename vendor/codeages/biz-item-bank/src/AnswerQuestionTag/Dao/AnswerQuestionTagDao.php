<?php

namespace Codeages\Biz\ItemBank\AnswerQuestionTag\Dao;

interface AnswerQuestionTagDao
{
    public function getByAnswerRecordId($answerRecordId);

    public function deleteByAnswerRecordId($answerRecordId);
}