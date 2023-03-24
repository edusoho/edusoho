<?php

namespace Biz\BehaviorVerification\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SmsBlackListDao extends GeneralDaoInterface
{
    public function getByIp($ip);
}