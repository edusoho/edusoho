<?php

namespace Biz\System\Dao\Impl;

use Biz\System\Dao\IpBlacklistDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class IpBlacklistDaoImpl extends GeneralDaoImpl implements IpBlacklistDao
{
    protected $table = 'ip_blacklist';

    public function getByIpAndType($ip, $type)
    {
        return $this->getByFields(array('ip' => $ip, 'type' => $type));
    }

    public function declares()
    {
        return array(
            'conditions' => array(
                'ip = :ip',
                'type = :type',
            ),
        );
    }
}
