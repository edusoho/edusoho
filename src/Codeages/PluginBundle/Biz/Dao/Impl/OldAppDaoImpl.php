<?php
namespace Codeages\PluginBundle\Biz\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use Codeages\PluginBundle\Biz\Dao\AppDao;

class AppDaoImpl extends GeneralDaoImpl implements AppDao
{
    protected $table = 'cloud_app';

    public function declares()
    {
        return array(
            'timestamps' => array(),
            'serializes' => array(),
            'orderbys' => array(),
            'conditions' => array(
            ),
        );
    }
}