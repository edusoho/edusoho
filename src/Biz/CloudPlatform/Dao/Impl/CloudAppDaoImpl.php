<?php

namespace Biz\CloudPlatform\Dao\Impl;

use Biz\CloudPlatform\Dao\CloudAppDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CloudAppDaoImpl extends GeneralDaoImpl implements CloudAppDao
{
    protected $table = 'cloud_app';

    public function getByCode($code)
    {
        return $this->getByFields(array(
            'code' => $code,
        ));
    }

    public function findByCodes(array $codes)
    {
        return $this->findInField('code', $codes);
    }

    public function findByTypes(array $types)
    {
        return $this->findInField('type', $types);
    }

    public function find($start, $limit)
    {
        return $this->search(array(), array('installedTime' => 'DESC'), $start, $limit);
    }

    public function countApps()
    {
        return $this->count(array());
    }

    public function declares()
    {
        return array(
            'orderbys' => array(
                'installedTime',
            ),
        );
    }
}
