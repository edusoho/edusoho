<?php

namespace Biz\MoneyCard\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MoneyCardBatchDao extends GeneralDaoInterface
{
    public function getBatchByToken($token, array $options = array());
}
