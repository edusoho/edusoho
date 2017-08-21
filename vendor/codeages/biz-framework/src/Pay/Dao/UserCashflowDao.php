<?php

namespace Codeages\Biz\Framework\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserCashflowDao extends GeneralDaoInterface
{
    public function findByTradeSn($sn);
}