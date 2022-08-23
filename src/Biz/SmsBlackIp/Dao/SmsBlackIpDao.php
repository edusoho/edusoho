<?php

namespace Biz\SmsBlackIp\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface SmsBlackIpDao extends GeneralDaoInterface
{
    public function getByIp($ip);
}