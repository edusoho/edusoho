<?php

namespace Codeages\Biz\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CashflowDao extends GeneralDaoInterface
{
    public function findByTradeSn($sn);

    public function sumColumnByConditions($column, $conditions);

    public function countUsersByConditions($conditions);

    public function sumAmountGroupByUserId($conditions);
}