<?php

namespace Codeages\PluginBundle\Biz\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\PluginBundle\Biz\Dao\OldAppDao;

class OldAppDaoImpl extends GeneralDaoImpl implements OldAppDao
{
    protected $table = 'cloud_app';

    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function findByType($type, $start, $limit)
    {
        return $this->search(array('type' => $type), array('installedTime' => 'ASC'), $start, $limit);
    }

    public function countByType($type)
    {
        return $this->count(array('type' => $type));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('installedTime', 'updatedTime'),
            'serializes' => array(),
            'orderbys' => array('installedTime'),
            'conditions' => array(
                'type = :type',
                'name = :name',
            ),
        );
    }
}
