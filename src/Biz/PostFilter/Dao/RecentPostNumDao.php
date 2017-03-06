<?php

namespace Biz\PostFilter\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface RecentPostNumDao extends GeneralDaoInterface
{
    public function getByIpAndType($ip, $type);
}
