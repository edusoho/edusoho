<?php

namespace Biz\AI\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AIAnswerRecordDao extends GeneralDaoInterface
{
    public function findByUserIdAndAppAndInputsHash($userId, $app, $inputsHash);
}
