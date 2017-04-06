<?php

namespace Biz\Cash\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CashChangeDao extends GeneralDaoInterface
{
    public function getByUserId($userId, array $options = array());

    public function waveCashField($id, $value);
}
