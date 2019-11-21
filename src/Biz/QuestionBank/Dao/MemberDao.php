<?php

namespace Biz\QuestionBank\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MemberDao extends GeneralDaoInterface
{
    public function findByBankId($bankId);

    public function findByUserId($userId);
}
