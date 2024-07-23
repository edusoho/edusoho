<?php

namespace Codeages\Biz\ItemBank\Answer\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AnswerRandomSeqRecordDao extends GeneralDaoInterface
{
    public function getByAnswerRecordId($answerRecordId);
}
