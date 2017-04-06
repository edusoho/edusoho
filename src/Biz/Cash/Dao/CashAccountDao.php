<?php

namespace Biz\Cash\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CashAccountDao extends GeneralDaoInterface
{
    public function getByUserId($userId, array $options = array());

    public function findByUserIds(array $userIds);

    public function waveCashField($id, $value);

    public function waveDownCashField($id, $value);
}
