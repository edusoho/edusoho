<?php

namespace Codeages\PluginBundle\Biz\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\PluginBundle\Biz\Dao\AppDao;

class AppDaoImpl extends GeneralDaoImpl implements AppDao
{
    protected $table = 'app';

    public function getByCode($code)
    {
        return $this->getByFields(array('code' => $code));
    }

    public function findByType($type, $start, $limit)
    {
        return $this->search(array('type' => $type), array('created_time' => 'ASC'), $start, $limit);
    }

    public function countByType($type)
    {
        return $this->count(array('type' => $type));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(),
            'orderbys' => array('created_time'),
            'conditions' => array(
                'type = :type',
                'name = :name',
            ),
        );
    }
}
