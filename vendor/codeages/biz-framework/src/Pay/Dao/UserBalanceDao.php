<?php

namespace Codeages\Biz\Framework\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserBalanceDao extends GeneralDaoInterface
{
    public function getByUserId($userId);

    public function findByUserIds($userIds);
}