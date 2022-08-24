<?php

namespace Biz\BehaviorVerification\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface BehaviorVerificationIpDao extends GeneralDaoInterface
{
    public function getByIp($ip);
}