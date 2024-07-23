<?php

namespace Codeages\Biz\ItemBank\Answer\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AnswerQuestionTagDao extends AdvancedDaoInterface
{
    public function getByAnswerRecordId($answerRecordId);
}
