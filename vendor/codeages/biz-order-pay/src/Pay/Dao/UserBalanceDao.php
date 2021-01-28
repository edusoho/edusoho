<?php

namespace Codeages\Biz\Pay\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface UserBalanceDao extends GeneralDaoInterface
{
    public function getByUserId($userId);

    public function findByUserIds($userIds);
}