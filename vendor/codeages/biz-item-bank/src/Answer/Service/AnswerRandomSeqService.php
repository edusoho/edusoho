<?php

namespace Codeages\Biz\ItemBank\Answer\Service;

interface AnswerRandomSeqService
{
    public function createAnswerRandomSeqRecordIfNecessary($answerRecordId);

    public function shuffleItemsAndOptions($assessment, $answerRecordId);
}
