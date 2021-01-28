<?php

namespace Biz\RewardPoint\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AccountDao extends AdvancedDaoInterface
{
    public function deleteByUserId($userId);

    public function getByUserId($userId, $potions = array());

    public function waveBalance($id, $value);

    public function waveDownBalance($id, $value);
}
