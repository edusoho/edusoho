<?php

namespace Biz\PostFilter\Dao\Impl;

use Biz\PostFilter\Dao\RecentPostNumDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class RecentPostNumDaoImpl extends GeneralDaoImpl implements RecentPostNumDao
{
    protected $table = 'recent_post_num';

    public function declares()
    {
        return array();
    }

    public function getByIpAndType($ip, $type)
    {
        return $this->getByFields(array('ip' => $ip, 'type' => $type));
    }
}
