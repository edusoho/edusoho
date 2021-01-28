<?php

namespace Codeages\Biz\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SecurityAnswerDao extends GeneralDaoInterface
{
    public function findByUserId($userId);

    public function getSecurityAnswerByUserIdAndQuestionKey($userId, $questionKey);
}