<?php

namespace Biz\Cash\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CashChangeDao extends GeneralDaoInterface
{
    public function getByUserId($userId, $lock = false);

    public function waveCashField($id, $value);
}