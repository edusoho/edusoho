<?php

namespace Biz\RewardPoint\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProductDao extends GeneralDaoInterface
{
    public function findByIds(array $ids);
}
