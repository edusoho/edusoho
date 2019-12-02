<?php

namespace Biz\QuestionBank\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MemberDao extends AdvancedDaoInterface
{
    public function getByBankIdAndUserId($bankId, $userId);

    public function findByBankId($bankId);

    public function findByUserId($userId);
}
