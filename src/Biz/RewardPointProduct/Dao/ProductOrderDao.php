<?php

namespace Biz\RewardPointProduct\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ProductOrderDao extends GeneralDaoInterface
{
    public function findByProductId($productId);

    public function findByUserId($userId);
}
