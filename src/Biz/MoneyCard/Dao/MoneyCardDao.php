<?php

namespace Biz\MoneyCard\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MoneyCardDao extends GeneralDaoInterface
{
    public function getMoneyCardByIds($ids);

    public function getMoneyCardByPassword($password);

    public function updateBatchByCardStatus($identifier, $fields);

    public function deleteMoneyCardsByBatchId($ids);

    public function isCardIdAvailable($ids);
}
