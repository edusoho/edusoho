<?php
namespace Codeages\PluginBundle\Biz\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\PluginBundle\Biz\Dao\AppDao;


class AppDaoImpl extends GeneralDaoImpl implements AppDao
{
    protected $table = 'app';

    public function getByCode($code)
    {
        return $this->getByFields(['code' => $code]);
    }

    public function declares()
    {
        return array(
            'timestamps' => array('created_time', 'updated_time'),
            'serializes' => array(),
            'conditions' => array(
                'name = :name',
            ),
        );
    }
}