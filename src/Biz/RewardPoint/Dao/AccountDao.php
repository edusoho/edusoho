<?php

namespace Biz\RewardPoint\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface AccountDao extends GeneralDaoInterface
{
    public function deleteByUserId($userId);

    public function getByUserId($userId, $potions = array());

    public function waveBalance($id, $value);

    public function waveDownBalance($id, $value);
}
