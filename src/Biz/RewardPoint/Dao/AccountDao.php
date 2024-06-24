<?php

namespace Biz\RewardPoint\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface AccountDao extends AdvancedDaoInterface
{
    public function deleteByUserId($userId);

    public function getByUserId($userId, $potions = []);

    public function waveBalance($id, $value);

    public function waveDownBalance($id, $value);

    public function countJoinUser($conditions);

    public function searchJoinUser($conditions, $orderBys, $start, $limit);
}
