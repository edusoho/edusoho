<?php

namespace Biz\AI\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AIAnswerResultDao extends GeneralDaoInterface
{
    public function findByAppAndInputsHash($app, $inputsHash);
}
