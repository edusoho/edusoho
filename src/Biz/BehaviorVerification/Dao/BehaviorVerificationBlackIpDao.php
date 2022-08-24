<?php

namespace Biz\BehaviorVerification\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface BehaviorVerificationBlackIpDao extends GeneralDaoInterface
{
    public function getByIp($ip);
}