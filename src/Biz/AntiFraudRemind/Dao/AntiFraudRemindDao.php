<?php

namespace Biz\AntiFraudRemind\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AntiFraudRemindDao extends GeneralDaoInterface
{
    public function getByUserId($userId);
}
